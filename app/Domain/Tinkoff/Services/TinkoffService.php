<?php

namespace App\Domain\Tinkoff\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Notification\Mail\AccountAlertsNotification;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\Tinkoff\Events\TinkoffApiSent;
use App\Domain\Tinkoff\Models\TinkoffLog;
use App\Domain\User\Models\User;
use App\Exception;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TinkoffService
{
    private const API_URL = 'https://openapi.tinkoff.ru/';
    private const HIDDEN_PLACEHOLDER = '<hidden>';
    private const TYPE_REFRESH_TOKEN = 'refresh_token';
    private const TYPE_OPERATIONS = 'operations';
    private const INCOME = '01';
    private const GET_FIRST_OPERATIONS_FROM_DATE = '2020-04-03';
    public $token;
    public $accountNumber;
    public $account;
    public $inn;
    public $email_admin;

    public const INFORMATION_PREFIX = 'Оплачено и начислено';

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function setAccountNumber($account)
    {
        $this->accountNumber = $account;

        return $this;
    }

    public function setAccount($account)
    {
        $this->account = $account;
        $user_id = AccountUser::where('account_id', $account)->where('role', 'admin')->first()->user_id;
        $this->email_admin = User::find($user_id)->email;

        return $this;
    }

    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }

    public function getAccountByCompany($company_id)
    {
        return \App\Domain\Company\Models\Company::find($company_id)->account_id;
    }

    public function accountStatement()
    {
        $params = [
            'grant_type' => self::TYPE_REFRESH_TOKEN,
            'refresh_token' => $this->token,
        ];

        $query = http_build_query($params);
        $url = self::API_URL.'sso//secure/token';
        $requestInfo = [
            'to' => $url,
            'payload' => $this->hideSensitiveInfo($params),
        ];
        $tinkoffLogRow = \App\Domain\Tinkoff\Models\TinkoffLog::create([
            'type' => self::TYPE_REFRESH_TOKEN,
            'request' => $requestInfo,
            'account_id' => $this->account,
        ]);
        $response = \Curl::to($url)
            ->withHeader('Content-Type: application/x-www-form-urlencoded')
            ->withData($query)
            ->post();

        $response_decode = json_decode($response);
        if (! isset($response_decode->access_token)) {
            $tinkoffLogRow->update([
                'response' => $this->hideSensitiveInfo($response_decode),
                'success' => false,
            ]);

            return;
        }

        $tinkoffLogRow->update([
            'response' => $this->hideSensitiveInfo($response_decode),
            'success' => true,
        ]);

        $previousOperationRequestRow = TinkoffLog::where(['type' => self::TYPE_OPERATIONS])
            ->where('id', '<', $tinkoffLogRow->id)
            ->where('success', true)
            ->where('account_id', $this->account)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        $previousRequestDate = $previousOperationRequestRow ?
            Carbon::parse($previousOperationRequestRow->created_at) :
            Carbon::parse(self::GET_FIRST_OPERATIONS_FROM_DATE);

        $params = [
            'Authorization' => $response_decode->access_token,
            'accountNumber' => $this->accountNumber,
            'from' => $previousRequestDate->format('Y-m-d+00:00:00'),
        ];
        $operationsUrl = self::API_URL.'sme/api/v1/partner/company/'.$this->inn.'/excerpt?';
        $requestInfo = [
            'to' => $operationsUrl,
            'payload' => $this->hideSensitiveInfo($params),
        ];
        $tinkoffLogRow = TinkoffLog::create([
            'type' => self::TYPE_OPERATIONS,
            'request' => $requestInfo,
            'account_id' => $this->account,
        ]);
        $response = \Curl::to($operationsUrl)
            ->withHeader('Authorization: Bearer '.$response_decode->access_token)
            ->withData($params)
            ->asJson()
            ->get();
        $tinkoffLogRow->update(['response' => $this->hideSensitiveInfo($response)]);

        $count_paid = 0;
        if (! is_object($response) || ! property_exists($response, 'operation') || ! $response->operation) {
            $tinkoffLogRow->update(['success' => true]);
            event(new TinkoffApiSent($count_paid));

            return;
        }

        $send_alert = false;
        foreach ($response->operation as $operation) {
            $isItPaymentFromLidogenerator = $operation->payerAccount === $this->accountNumber;
            $isIncome = $operation->operationType === self::INCOME;
            if ($isItPaymentFromLidogenerator || ! $isIncome) {
                continue;
            }
            $id = '';
            preg_match_all('/\d{1,}/', $operation->paymentPurpose, $matches, PREG_SET_ORDER);
            if ($matches[0][0]) {
                $id = preg_replace('/[^0-9]/', '', $matches[0][0]);
            }

            if (is_numeric($id)) {
                $payment_transaction = \App\Domain\Finance\Models\PaymentTransaction::where('id', $id)
                    ->where('status', \App\Domain\Finance\Models\PaymentTransaction::STATUS_NOT_PAID)
                    ->first();
                if ($payment_transaction) {
                    $count_paid++;
                    $payment_transaction->status = \App\Domain\Finance\Models\PaymentTransaction::STATUS_PAID;
                    $payment_transaction->operation = \App\Domain\Finance\Models\PaymentTransaction::OPERATION_REPLENISHMENT;
                    $payment_transaction->save();
                    $company = \App\Domain\Company\Models\Company::find($payment_transaction->company_id);

                    $account_id = $this->getAccountByCompany($company->id);
                    if ($account_id != $this->account) {
                        $this->addToLog([
                                            'company' => $company->name."(#{$company->id})",
                                            'payment_transaction_id' => $payment_transaction->id,
                                            'message' => 'Внимание! Счет '.$payment_transaction->id
                                .' выставлен аккаунтом '.Account::find($account_id)->name
                                .', но оплату этого счета нашли в аккаунте '. \App\Domain\Account\Models\Account::find($this->account)->name,
                        ]);

                        return;
                    }

                    $this->addToLog([
                        'company' => $company->name."(#{$company->id})",
                        'payment_transaction_id' => $payment_transaction->id,
                        'payment_transaction_amount' => $payment_transaction->amount,
                        'incoming_amount' => $operation->amount,
                    ]);

                    $resultInformation = $payment_transaction->information;
                    $resultInformation .= "<br>" . self::INFORMATION_PREFIX . " {$operation->amount} из {$payment_transaction->amount}<br>На счет поступили {$operation->chargeDate}";
                    $payment_transaction->update(['information' => $resultInformation, 'amount' => $operation->amount]);

                    (new UpdateCompanyBalanceAction())->execute($company, $operation->amount);
                }
            } else {
                $send_alert = true;
            }

            if ($send_alert) {
                $data = [
                    'payerName' => $operation->payerName,
                    'payerInn' => $operation->payerInn,
                    'paymentPurpose' => $operation->paymentPurpose,
                ];

                try {
                    \Mail::to($this->email_admin)->queue(
                        new AccountAlertsNotification((object) $data)
                    );
                } catch (Exception $e) {
                }
            }
        }

        $tinkoffLogRow->update(['success' => true]);
        event(new TinkoffApiSent($count_paid));
    }

    //todo: use Collection here
    public function hideSensitiveInfo($payload): array
    {
        $payload = (array) $payload;
        $result = [];
        foreach ($payload as $key => $value) {
            $isSensitiveField = stripos($key, 'token') !== false;
            $isSensitiveField = $isSensitiveField || stripos($key, 'authorization') !== false;
            if ($isSensitiveField) {
                $result[$key] = self::HIDDEN_PLACEHOLDER;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function addToLog($payload)
    {
        $tinkoffLog = new Logger('tinkoff');
        $tinkoffLog->pushHandler(new StreamHandler(storage_path('logs/'.$this->account.'_tinkoffapi.log')), Logger::INFO);
        $tinkoffLog->info('Processing incoming transaction', $payload);
    }
}
