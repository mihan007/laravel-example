<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AdminBalanceLimitCheck extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var Company
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
     * @param \App\Domain\Company\Models\Company $company
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
        return $this->subject('Приостановить рекламу | '.$this->company->name)
            ->markdown('emails.admin-balance-limit-check')->to($this->email);
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return true;
    }
}
