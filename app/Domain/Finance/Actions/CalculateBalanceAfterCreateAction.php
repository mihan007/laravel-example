<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Finance\Models\PaymentTransaction;

class CalculateBalanceAfterCreateAction
{
    public function execute(PaymentTransaction $paymentTransaction)
    {
        $isInvoiced = ($paymentTransaction->status === PaymentTransaction::STATUS_NOT_PAID);
        if ($paymentTransaction->isReduceBalance()) {
            $paymentTransaction->balance = (float)$paymentTransaction->company->balance - (float)$paymentTransaction->amount;
        } elseif (!$isInvoiced) {
            $paymentTransaction->balance = (float)$paymentTransaction->company->balance + (float)$paymentTransaction->amount;
        } else {
            $paymentTransaction->balance = (float)$paymentTransaction->company->balance;
        }
    }
}
