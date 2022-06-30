<?php

namespace App\Domain\Finance\Observers;

use App\Domain\Company\Jobs\CompanyReportRebuilder;
use App\Domain\Finance\Actions\CalculateBalanceAfterCreateAction;
use App\Domain\Finance\Actions\CalculateBalanceAfterUpdateAction;
use App\Domain\Finance\Models\PaymentTransaction;

/**
 * Class PaymentTransactionObserver.
 */
class PaymentTransactionObserver
{
    /**
     * @param PaymentTransaction $paymentTransaction
     */
    public function creating(PaymentTransaction $paymentTransaction)
    {
        (new CalculateBalanceAfterCreateAction())->execute($paymentTransaction);
    }

    /**
     * @param \App\Domain\Finance\Models\PaymentTransaction $paymentTransaction
     */
    public function created(PaymentTransaction $paymentTransaction)
    {
        $this->updateCompanyReport($paymentTransaction);
    }

    /**
     * @param \App\Domain\Finance\Models\PaymentTransaction $paymentTransaction
     */
    public function updating(PaymentTransaction $paymentTransaction)
    {
        (new CalculateBalanceAfterUpdateAction())->execute($paymentTransaction);
    }

    /**
     * @param \App\Domain\Finance\Models\PaymentTransaction $paymentTransaction
     */
    public function updated(PaymentTransaction $paymentTransaction)
    {
        $this->updateCompanyReport($paymentTransaction);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     */
    public function deleted(PaymentTransaction $paymentTransaction)
    {
        $this->updateCompanyReport($paymentTransaction);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    private function updateCompanyReport(PaymentTransaction $paymentTransaction
    ): \Illuminate\Foundation\Bus\PendingDispatch {
        $startAt = $paymentTransaction->proxyLead ? $paymentTransaction->proxyLead->created_at : $paymentTransaction->created_at;
        $endAt = $paymentTransaction->proxyLead ? $paymentTransaction->proxyLead->updated_at : $paymentTransaction->updated_at;

        return CompanyReportRebuilder::dispatch(
            $startAt,
            $endAt,
            $paymentTransaction->company
        )->delay(now()->addSeconds(10));
    }
}
