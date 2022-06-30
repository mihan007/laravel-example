<?php

namespace App\Domain\Company\Listeners\Reconciliation;

use App\Domain\Company\Events\StoreReconciliationEvent;
use App\Domain\ProxyLead\Models\Reconclication;

class CreateReconciliationListener
{
    /**
     * Handle the event.
     *
     * @param StoreReconciliationEvent $event
     * @return void
     */
    public function handle(StoreReconciliationEvent $event)
    {
        Reconclication::create([
            'proxy_lead_setting_id' => $event->company->proxyLeadSettings->id,
            'period' => $event->period->toDateString(),
            'type' => 'admin',
        ]);
    }
}
