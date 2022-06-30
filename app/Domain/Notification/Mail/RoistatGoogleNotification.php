<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Roistat\Models\RoistatGoogleAnalytic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RoistatGoogleNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Store latest company yandex balance.
     *
     * @var \App\Domain\Roistat\Models\RoistatGoogleAnalytic
     */
    public $latestAnalyticData;

    /**
     * Store company information.
     *
     * @var Company
     */
    public $companyInformation;
    public $email;
    public $unsubscribeUrl;
    public $notificationSettingsUrl;
    public $disabledLinkUrl;

    /**
     * Create a new message instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param \App\Domain\Roistat\Models\RoistatGoogleAnalytic $latest
     * @param $email
     */
    public function __construct(Company $company, RoistatGoogleAnalytic $latest, $email = '')
    {
        $this->latestAnalyticData = $latest;
        $this->companyInformation = $company;
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'roistat_google', $company->id);
        $this->companyInformation->changeEmailNotificationLastSend(
            EmailNotification::ROISTAT_GOOGLE_TYPE,
            $this->email
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (! $this->unsubscribeUrl) {
            $email = $this->companyInformation->account->warningEmail;

            return $this->subject('Внимание! Нет получателей уведомления "Google Баланс"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject('На вашем счету Google закончились средства')
            ->markdown('emails.roistat-google')->to($this->email);
    }

    public function getNotificationType()
    {
        return EmailNotification::ROISTAT_GOOGLE_TYPE;
    }

    public function getCompanyId()
    {
        return $this->companyInformation->id;
    }
}
