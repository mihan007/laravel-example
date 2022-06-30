<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class NewProxyLeadMail extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var ProxyLead
     */
    public $proxyLead;
    public $company;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;
    public $isUserCustomer;

    /**
     * Create a new message instance.
     *
     * @param ProxyLead $proxyLead
     * @param string $email
     */
    public function __construct(ProxyLead $proxyLead, $email = '')
    {
        $this->proxyLead = $proxyLead;
        $this->proxyLead->load('proxyLeadSetting.company');
        $this->company = $proxyLead->proxyLeadSetting->company;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'proxy_leads', $this->company->id);
        $recipientAtOurSystem = User::whereEmail($email)->first();
        $this->isUserCustomer = $recipientAtOurSystem ? $recipientAtOurSystem->isCustomer() : true;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (! $this->unsubscribeUrl) {
            $email = $this->company->account->warningEmail;

            return $this->subject('Внимание! Нет получателей уведомления "Заявки от клиентов"')
                ->markdown('emails.notification-error')->to($email);
        }

        if ($this->isSendStopLeadsNotification()) {
            return $this->subject('Новая заявка с сайта: '.$this->proxyLead->title)
                ->markdown('emails.proxy-lead.new_stop_leads')->to($this->email);
        } else {
            return $this->subject('Новая заявка с сайта: '.$this->proxyLead->title)
                ->markdown('emails.proxy-lead.new')->to($this->email);
        }
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return true;
    }

    private function isSendStopLeadsNotification()
    {
        $status = false;
        $last_transaction = $this->getLastTransaction();
        if ($last_transaction && $last_transaction->operation === 'write-off') {
            $before_transaction_balance = $last_transaction->amount + $this->proxyLead->proxyLeadSetting->company->balance;
            if ($before_transaction_balance < $this->proxyLead->proxyLeadSetting->company->amount_limit
                && ! $this->proxyLead->proxyLeadSetting->company->free_period) {
                $status = true;
            }
        } elseif ($this->proxyLead->proxyLeadSetting->company->balance < $this->proxyLead->proxyLeadSetting->company->amount_limit
            && ! $this->proxyLead->proxyLeadSetting->company->free_period) {
            $status = true;
        }

        return $status;
    }

    private function getLastTransaction()
    {
        return PaymentTransaction::query()
            ->where('company_id', '=', $this->company->id)
            ->where('proxy_leads_id', '=', $this->proxyLead->id)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
