<?php

namespace App\Domain\ProxyLead\Listeners;

use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\Notification\Mail\MailCheckPayment;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\ProxyLeadSender;

class SendProxyLeadListener
{
    const DOUBLE_COMPANY_CONFIRMED = 6;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CreateProxyLeadEvent $event
     * @return void
     * @throws \Yclients\YclientsException
     */
    public function handle(CreateProxyLeadEvent $event)
    {
        $sender = new ProxyLeadSender($event->proxyLeadSetting, $event->proxyLead);
        $sender->send();

        if ($event->proxyLeadSetting->company->prepayment && $event->proxyLeadSetting->company->free_period) {
            $report_lead = PlReportLead::where('proxy_lead_id', $event->proxyLead->id)->first();

            if ($report_lead && $report_lead->company_confirmed != self::DOUBLE_COMPANY_CONFIRMED) {
                $payment_transaction = PaymentTransaction::where('information', 'Целевая заявка №'.$event->proxyLead->id)->first();
                $shouldWeNotifyAboutMissingLeadTransaction = $event->proxyLead->company->shouldWeNotifyAboutMissingLeadTransaction();
                if (! $payment_transaction && $shouldWeNotifyAboutMissingLeadTransaction) {
                    $email = $event->proxyLead->company->account->warningEmail;
                    \Mail::to($email)->send(new MailCheckPayment($event->proxyLead, $event->proxyLeadSetting));
                }
            }
        }
    }
}
