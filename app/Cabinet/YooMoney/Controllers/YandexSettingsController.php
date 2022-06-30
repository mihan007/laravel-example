<?php

namespace App\Cabinet\YooMoney\Controllers;

use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\YooMoney\Models\YandexSetting;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class YandexSettingsController extends Controller
{
    public function processWebhook(Request $request, $id)
    {
        $yandexSetting = YandexSetting::where('account_id', $id)
            ->where('is_active', 1)
            ->firstOrFail();
        $hash = $this->calcHash($request, $yandexSetting->secret_key);
        if ($request->sha1_hash != $hash) {
            abort(403);
        }

        $payment_transaction_id = $request->label;
        $protectedWithCode = $request->codepro && $request->codepro !== 'false';
        if ($request->unaccepted === 'true') {
            $logMessage = 'Перевод еще не зачислен на счет получателя: '.json_encode($request->toArray()).PHP_EOL;
        } elseif ($protectedWithCode) {
            $logMessage = 'Перевод защищен кодом протекции:'.json_encode($request->toArray()).PHP_EOL;
        } else {
            /** @var \App\Domain\Finance\Models\PaymentTransaction $paymentTransaction */
            $paymentTransaction = PaymentTransaction::find($payment_transaction_id);
            if (! $paymentTransaction) {
                $logMessage = 'Неизвестный платеж: '.json_encode($request->toArray()).PHP_EOL;
            } else {
                $addToBalance = $request->withdraw_amount;
                $paymentTransaction->amount = $request->withdraw_amount;
                $paymentTransaction->status = PaymentTransaction::STATUS_PAID;
                $paymentTransaction->operation = PaymentTransaction::OPERATION_REPLENISHMENT;
                if ($paymentTransaction->save()) {
                    (new UpdateCompanyBalanceAction())->execute($paymentTransaction->company, $addToBalance);
                    $logMessage = $paymentTransaction->id.': Зачислено '.$addToBalance.' рублей. Операция успешна:'.json_encode($request->toArray()).PHP_EOL;
                } else {
                    $logMessage = $paymentTransaction->id.': Ошибка! На зачислено '.$addToBalance.' рублей. Операция провалилась:'.json_encode($request->toArray()).PHP_EOL;
                }
            }
        }

        file_put_contents(storage_path('logs/yandex_notifications.log'), $logMessage, FILE_APPEND);
    }

    private function calcHash(Request $request, $notificationSecret)
    {
        $params = [
            'notification_type',
            'operation_id',
            'amount',
            'currency',
            'datetime',
            'sender',
            'codepro',
            'notification_secret',
            'label',
        ];
        $resultParts = [];
        foreach ($params as $param) {
            if ($param === 'notification_secret') {
                $resultParts[] = $notificationSecret;
                continue;
            }
            if (! $request->has($param)) {
                return 'null';
            }
            $resultParts[] = $request->get($param);
        }

        return sha1(implode('&', $resultParts));
    }
}
