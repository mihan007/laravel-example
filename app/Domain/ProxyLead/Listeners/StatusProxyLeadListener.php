<?php

namespace App\Domain\ProxyLead\Listeners;

use App\Domain\ProxyLead\Events\StatusProxyLeadEvent;
use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Contracts\Queue\ShouldQueue;

class StatusProxyLeadListener implements ShouldQueue
{
    /**
    * @var \App\Domain\ProxyLead\Models\ProxyLead
    */
    private $proxyLead;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  StatusProxyLeadEvent  $event
     * @return void
     */
    public function handle(StatusProxyLeadEvent $event)
    {
        ProxyLead::orderBy('id', 'desc')->chunk(100, function ($proxyLeads) {
            foreach ($proxyLeads as $proxyLead) {
                $reportLead = $proxyLead->reportLead;
                $companyConfirmed = $reportLead->company_confirmed;
                $notBeforeCalledCounter = $reportLead->not_before_called_counter;
                $adminConfirmed = $reportLead->admin_confirmed;
                $status = 0;

                if ($adminConfirmed == 1) {
                    if ($companyConfirmed === 5 && $notBeforeCalledCounter < 4) {
                        $status = 1;
                    } elseif ($companyConfirmed === 1) {
                        $status = 1;
                    } else {
                        $status = 1;
                    }
                }

                if ($adminConfirmed == 2) {
                    $status = 2;
                }

                if ($adminConfirmed == 0) {
                    if ($companyConfirmed === 1) {
                        $status = 0;
                    } else {
                        $status = 2;
                    }
                }

                $proxyLead->update([
                    'status' => $status
                ]);
            }
        });
    }
}
