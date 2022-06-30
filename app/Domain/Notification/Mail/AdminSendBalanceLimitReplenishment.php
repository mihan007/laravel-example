<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AdminSendBalanceLimitReplenishment extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    public $company;
    public $period;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param Company $company
     * @param $email
     */
    public function __construct(Company $company, $email)
    {
        $this->company = $company;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'main', $company->id);
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Включить рекламу | '.$this->company->name)
            ->markdown('emails.admin-balance-limit-replenishment')->to($this->email);
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
