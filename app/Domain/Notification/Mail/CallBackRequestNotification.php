<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class CallBackRequestNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Company
     */
    public $company;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
     */
    public $proxy_lead;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param Company $company
     * @param $proxy_lead
     * @param string $email
     */
    public function __construct(Company $company, $proxy_lead, $email = '')
    {
        $this->company = $company;
        $this->proxy_lead = $proxy_lead;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'proxy_leads', $company->id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (! $this->email) {
            $email = $this->company->account->warningEmail;

            return $this->subject('Внимание! Нет получателей уведомления "Нужно перезвонить по заявке"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject($this->getSubject())
            ->markdown('emails.callback-request')->to($this->email);
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }

    /**
     * @return string
     */
    private function getSubject(): string
    {
        $leadDetails = $this->proxy_lead->id;
        if ($this->proxy_lead->phone) {
            $leadDetails .= ' по номеру '.$this->proxy_lead->readablePhone;
        }

        return 'Нужно перезвонить по заявке '.$leadDetails.' и уточнить статус звонка';
    }
}
