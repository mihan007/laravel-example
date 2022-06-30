<?php

namespace App\Domain\Company\Listeners\Reconciliation;

use App\Domain\Company\Events\StoreReconciliationEvent;
use App\Domain\Finance\FinanceReportCreator;

class FinanceReportRegenerateListener
{
    /**
     * Handle the event.
     *
     * @param StoreReconciliationEvent $event
     * @return void
     */
    public function handle(StoreReconciliationEvent $event)
    {
        (new FinanceReportCreator($event->company, $event->period))->create();
    }
}
