<?php

namespace App\Console\Commands;

use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\Notification\Mail\CallBackRequestNotification;
use App\Domain\Notification\Mail\MailCheckPayment;
use App\Domain\ProxyLead\Balance;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\ProxyLead\Models\PlReportLead;
use Carbon\Carbon;
use Mail;

class CallbackReminder
{
    public const MAX_CALLS_COUNTER = 4;
    public const MAX_NOTIFICATION_HOURS = 168;
    public const CALL_INTERVAL_HOURS = 3;

    public function remind(): void
    {
        $callDidBefore = Carbon::now()->subHours(self::CALL_INTERVAL_HOURS);
        $callDidAfter = Carbon::now()->subHours(self::MAX_NOTIFICATION_HOURS);

        $plReportLeads = PlReportLead::query()
            ->whereCompanyConfirmed(PlReportLead::COMPANY_STATUS_COULD_NOT_REACH_BY_PHONE)
            ->where('not_before_called_counter', '<', self::MAX_CALLS_COUNTER)
            ->where('updated_at', '<', $callDidBefore)
            ->where('updated_at', '>', $callDidAfter)
            ->whereHas('proxyLead')
            ->whereHas('proxyLead.proxyLeadSetting')
            ->whereHas('proxyLead.proxyLeadSetting.company')
            ->get();

        /** @var PlReportLead $plReportLead */
        foreach ($plReportLeads as $plReportLead) {
            $proxyLeadSetting = $plReportLead->proxyLead->proxyLeadSetting;
            $company = $proxyLeadSetting->company;
            $emails = $company->recipientsNotifications()->get();
            $notifyEmails = $emails->isEmpty() ? [''] : $emails;
            foreach ($notifyEmails as $notifyEmail) {
                if (!$notifyEmail->email) {
                    continue;
                }
                echo "Notify $notifyEmail->email about lead {$plReportLead->proxyLead->id}\n";
                Mail::send(new CallBackRequestNotification($company, $plReportLead->proxyLead, $notifyEmail));
            }
            $plReportLead->userConfirmation(PlReportLead::STATUS_AGREE);
            $company = $plReportLead->proxyLead->company;
            new Balance($company, $plReportLead->proxyLead, false);
            new BalanceNotifier($company);
            $payment_transaction = PaymentTransaction::query()
                ->where('information', 'Целевая заявка №' . $plReportLead->proxyLead->id)
                ->first();
            $shouldWeNotifyAboutMissingLeadTransaction = $plReportLead
                ->proxyLead
                ->company
                ->shouldWeNotifyAboutMissingLeadTransaction();
            if (!$payment_transaction && $shouldWeNotifyAboutMissingLeadTransaction) {
                $email = $plReportLead->proxyLead->company->account->warningEmail;
                Mail::to($email)->send(new MailCheckPayment($plReportLead->proxyLead, $proxyLeadSetting));
            }
        }
    }
}
