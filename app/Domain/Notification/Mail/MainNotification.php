<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class MainNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    public $company;
    /**
     * @var string
     */
    public $emailMessage;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param Company $company
     * @param $message
     * @param $email
     */
    public function __construct(Company $company, $message, $email)
    {
        $this->company = $company;
        $this->emailMessage = $message;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'main', $company->id);
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

            return $this->subject('Внимание! Нет получателей уведомления "Отчет администратора"')
                ->markdown('emails.notification-error')->to($email);
        }

        $subject = "У компании {$this->company->name} наблюдаются проблемы";

        return $this->subject($subject)
            ->markdown('emails.main')->to($this->email);
    }

    public function getNotificationType()
    {
        return EmailNotification::MAIN_TYPE;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }
}
