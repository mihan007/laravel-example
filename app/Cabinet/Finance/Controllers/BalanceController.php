<?php

namespace App\Cabinet\Finance\Controllers;

use App\Domain\Account\Models\AboutCompany;
use App\Domain\Account\Models\AccountSetting;
use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\Notification\Mail\CustomerBalanceNotification;
use App\Domain\YooMoney\Services\YooMoney;
use App\Http\Requests\MarkInvoiceRequest;
use App\Http\Requests\ReplenishBalanceRequest;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use NumberToWords\NumberToWords;
use PDF;

class BalanceController extends Controller
{
    public function replenish($accountId, Company $company, ReplenishBalanceRequest $request)
    {
        $operation = $request->post('operation');
        $paymentType = $request->post('payment_type');

        $payment_transaction = new PaymentTransaction();
        $payment_transaction->company_id = $company->id;
        $payment_transaction->payment_type = $paymentType;
        $payment_transaction->amount = (int)$request->post('amount');
        $payment_transaction->company_name = $request->post('company_name');
        $payment_transaction->company_inn = $request->post('company_inn');
        $payment_transaction->status = PaymentTransaction::STATUS_NOT_PAID;

        if ($paymentType === PaymentTransaction::PAYMENT_TYPE_MANUAL) {
            if ($operation === PaymentTransaction::OPERATION_REPLENISHMENT) {
                $payment_transaction->operation = PaymentTransaction::OPERATION_REPLENISHMENT;
                $payment_transaction->status = PaymentTransaction::STATUS_PAID;
            } else {
                $payment_transaction->operation = PaymentTransaction::OPERATION_WRITE_OFF;
                $payment_transaction->status = PaymentTransaction::STATUS_WRITE_OFF;
            }

            $payment_transaction->information = Auth::user()->name;
        }
        $payment_transaction->save();

        switch ($paymentType) {
            case PaymentTransaction::PAYMENT_TYPE_YANDEX_MONEY:
            case PaymentTransaction::PAYMENT_TYPE_CREDIT_CARD:
                $redirectUrl = $this->payWithYandex($accountId, $payment_transaction, $request);

                return ['url' => $redirectUrl];
            case PaymentTransaction::PAYMENT_TYPE_TINKOFF:
                $url = route('user.invoicePdf', [
                    'paymentTransaction' => $payment_transaction->id,
                    'company' => $company
                ]);
                $payment_transaction->information = "№ счета <a target='_blank' href='{$url}'>{$payment_transaction->id}</a>"
                    . "<br>ИНН: {$payment_transaction->company_inn}"
                    . "<br>Компания {$payment_transaction->company_name}";
                $payment_transaction->saveQuietly();

                return ['url' => $url];
            case PaymentTransaction::PAYMENT_TYPE_MANUAL:
                abort_if(current_user_is_client(), 403);
                $amount = ($operation === PaymentTransaction::OPERATION_REPLENISHMENT)
                    ? $payment_transaction->amount
                    : -$payment_transaction->amount;
                (new UpdateCompanyBalanceAction())->execute($company, $amount);
                break;
            default:
                abort(400, 'Unknown payment type');
        }
    }

    public function payWithYandex($account_id, PaymentTransaction $paymentTransaction,  $request)
    {
        return (new YooMoney)->setAccount($account_id)
            ->setup($paymentTransaction)
            ->pay($paymentTransaction->amount);
    }

    public function markInvoice($accountId, Company $company, MarkInvoiceRequest $request)
    {
        $paymentTransaction = PaymentTransaction::findOrFail($request->id);
        if ($paymentTransaction->payment_type !== PaymentTransaction::PAYMENT_TYPE_TINKOFF) {
            abort(400);
        }
        if ($paymentTransaction->paidByTinkoff) {
            abort(400);
        }
        if ($request->action === 'paid') {
            if ($paymentTransaction->status === PaymentTransaction::STATUS_NOT_PAID) {
                $paymentTransaction->status = PaymentTransaction::STATUS_PAID;
                $paymentTransaction->operation = PaymentTransaction::OPERATION_REPLENISHMENT;
                $paymentTransaction->save();
                (new UpdateCompanyBalanceAction())
                    ->execute($paymentTransaction->company, $paymentTransaction->amount);
            }
        } else {
            if ($paymentTransaction->status === PaymentTransaction::STATUS_PAID) {
                $paymentTransaction->status = PaymentTransaction::STATUS_NOT_PAID;
                $paymentTransaction->operation = null;
                $paymentTransaction->save();
                (new UpdateCompanyBalanceAction())
                    ->execute($paymentTransaction->company, -$paymentTransaction->amount);
            }
        }
    }

    /**
     * Создание инвойса PDF.
     * @param $public_id
     * @param $id
     * @param null $download
     * @return \Illuminate\Http\Response|void
     */
    public function invoice(Company $company, PaymentTransaction $paymentTransaction, $download = null)
    {
        abort_unless($company->id === $paymentTransaction->company_id, 403);

        $payment_transaction_create_date = strtotime($paymentTransaction->created_at);
        $date = date('d', $payment_transaction_create_date) . ' ' . $this->getRusMonth(
                date('m', $payment_transaction_create_date)
            ) . ' ' . date('Y', $payment_transaction_create_date);

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('ru');
        $str_price = $numberTransformer->toWords($paymentTransaction->amount);

        $account_setting = $company->account->accountSetting;
        $about_company = $company->account->aboutCompany;

        $data = [
            'inn' => $paymentTransaction->company_inn,
            'company' => $paymentTransaction->company_name,
            'number_invoice' => $paymentTransaction->id,
            'date' => $date,
            'amount' => $paymentTransaction->amount,
            'str_price' => $str_price,
            'account_setting' => $account_setting,
            'about_company' => $about_company,
        ];

        $pdf = PDF::loadView('pdf.invoice', $data);

        return $pdf->stream();
    }

    public function getRusMonth($month)
    {
        $months = [
            'января' => '01',
            'февраля' => '02',
            'марта' => '03',
            'апреля' => '04',
            'мая' => '05',
            'июня' => '06',
            'июля' => '07',
            'августа' => '08',
            'сентября' => '09',
            'октября' => '10',
            'ноября' => '11',
            'декабря' => '12',
        ];

        return array_search($month, $months);
    }

    public function notificationYandex(Request $request)
    {
        file_put_contents(storage_path('logs/yandex.log'), print_r($request->all(), 1), FILE_APPEND);
        $sha1 = sha1(
            $request->post('notification_type') . '&' . $request->post('operation_id') . '&' .
            $request->post('amount') . '&' . $request->post('currency') . '&' . $request->post('datetime') . '&'
            . $request->post('sender') . '&' . $request->post('codepro') . '&' . env('SECRET_KEY_YANDEX_MONEY')
            . '&' . $request->post('label')
        );

        if ($sha1 == $request->post('sha1_hash')) {
            $payment_transaction = PaymentTransaction::find($request->post('label'));
            if ($payment_transaction) {
                $payment_transaction->status = 'paid';
                $payment_transaction->created_at = date('Y-m-d H:i:s');
                $payment_transaction->operation = 'replenishment';
                $paidAmount = $request->post('amount');
                $payment_transaction->amount = $paidAmount;
                $payment_transaction->save();

                $company = Company::find($payment_transaction->company_id);
                (new UpdateCompanyBalanceAction())->execute($company, $paidAmount);
            }
        }
    }

    public function check()
    {
        $companies = Company::where('prepayment', 1)->where('free_period', 0)->get();
        foreach ($companies as $company) {
            if ($company->balance == null || $company->balance_limit == null) {
                continue;
            }

            if ($company->balance <= $company->balance_limit) {
                if (!$company->customerBalanceLimitNotifications) {
                    Mail::send(new CustomerBalanceNotification($company));
                }

                foreach ($company->customerBalanceLimitNotifications as $emailNotification) {
                    Mail::send(
                        new CustomerBalanceNotification($company, $emailNotification->email)
                    );
                }
            }
        }
    }
}
