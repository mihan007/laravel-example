<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

// todo: rename ModerarionApplicationsAdmin to ModerationApplicationsAdmin
class ModerarionApplicationsAdmin extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $company;
    public $ids;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param $ids
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxy_leads_setting
     * @param string $email
     */
    public function __construct($ids, ProxyLeadSetting $proxy_leads_setting, $email = '')
    {
        $this->company = $proxy_leads_setting->company;
        $this->ids = $ids;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'main', $this->company->id);
        $this->email = $email;
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

            return $this->subject('Внимание! Нет получателей уведомления "Модерация заявок"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject('Модерация заявок - '.$this->company->name)
            ->markdown('emails.proxy-lead.moderation_admin')->to($this->email);
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }
}
