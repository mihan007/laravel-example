<?php

namespace App\Domain\Bitrix\Listeners;

use App\Domain\Bitrix\BitrixLogTrait;
use App\Domain\Notification\ProxyLead\SendProxyLeadToBitrixJob;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Models\ProxyLead;
use Yclients\YclientsException;

class BitrixListener
{
    use BitrixLogTrait;

    /**
     * Handle the event.
     *
     * @param  CreateProxyLeadEvent $event
     * @return void
     * @throws YclientsException
     */
    public function handle(CreateProxyLeadEvent $event)
    {
        if (! $this->canWeSendToBitrix($event->proxyLead)) {
            return;
        }

        SendProxyLeadToBitrixJob::dispatch($event->proxyLeadSetting, $event->proxyLead);
    }

    private function canWeSendToBitrix(ProxyLead $proxyLead)
    {
        $proxyLeadSettings = $proxyLead->proxyLeadSetting;

        $this->log($proxyLeadSettings->attributesToArray(), 'Proxy lead created. Here is settings:', $proxyLeadSettings, $proxyLead);
        if ($proxyLeadSettings === null || empty($proxyLeadSettings->bitrix_webhook)) {
            $this->log([], 'Sending to bitrix disabled because $proxyLeadSettings === null || empty($proxyLeadSettings->bitrix_webhook)', $proxyLeadSettings, $proxyLead);

            return false;
        }

        $this->log($proxyLeadSettings->company->attributesToArray(), 'Proxy company:', $proxyLeadSettings, $proxyLead);
        $isPostpaid = ! $proxyLeadSettings->company->prepayment;
        $isTrial = $proxyLeadSettings->company->free_period;
        $this->log(compact('isPostpaid', 'isTrial'), 'Check if postpaid or trial:', $proxyLeadSettings, $proxyLead);
        if ($isPostpaid || $isTrial) {
            return true;
        }

        $companyBalance = $proxyLeadSettings->company->balance;
        $companyBalanceNotificationLimit = $proxyLeadSettings->company->amount_limit;
        $this->log(compact('companyBalance', 'companyBalanceNotificationLimit'), 'Check if balance enough:', $proxyLeadSettings, $proxyLead);
        if ($companyBalance < $companyBalanceNotificationLimit) {
            return false;
        }

        $this->log($proxyLead->attributesToArray(), 'Send to bitrix lead:', $proxyLeadSettings, $proxyLead);

        return true;
    }
}
