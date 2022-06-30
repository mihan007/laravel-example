<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class CustomerBalanceNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    public $company;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param string $email
     */
    public function __construct(Company $company, $email = '')
    {
        $this->company = $company;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'customer_balance', $company->id);
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

            return $this->subject('Внимание! Нет получателей уведомления "Минимальный баланс"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject('Минимальный баланс!')
            ->markdown('emails.customer-balance')->to($this->email);
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
