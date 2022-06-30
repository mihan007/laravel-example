<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Finance\Models\PaymentTransaction;

class CalculateBalanceAfterUpdateAction
{
    public function execute(PaymentTransaction $paymentTransaction)
    {
        $isMoneyBack = ($paymentTransaction->status === PaymentTransaction::STATUS_NOT_PAID)
            && ($paymentTransaction->payment_type === PaymentTransaction::PAYMENT_TYPE_TINKOFF);
        if ($paymentTransaction->isReduceBalance() || $isMoneyBack) {
            $paymentTransaction->balance = (float)$paymentTransaction->company->balance - (float)$paymentTransaction->amount;
        } else {
            $paymentTransaction->balance = (float)$paymentTransaction->company->balance + (float)$paymentTransaction->amount;
        }
    }
}
