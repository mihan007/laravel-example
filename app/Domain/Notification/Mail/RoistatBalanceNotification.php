<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RoistatBalanceNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    public $company;

    /**
     * @var
     */
    public $currentRoistatBalance;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param $currentRoistatBalance
     * @param $email
     */
    public function __construct(Company $company, $currentRoistatBalance, $email = '')
    {
        $this->company = $company;
        $this->currentRoistatBalance = $currentRoistatBalance;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'roistat_balance', $company->id);
        $this->company->changeEmailNotificationLastSend(EmailNotification::ROISTAT_BALANCE_TYPE, $this->email);
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

            return $this->subject('Внимание! Нет получателей уведомления "Roistat баланс"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject('На вашем счету Roistat закончились средства')
            ->markdown('emails.roistat-balance')->to($this->email);
    }

    public function getNotificationType()
    {
        return EmailNotification::ROISTAT_BALANCE_TYPE;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }
}
