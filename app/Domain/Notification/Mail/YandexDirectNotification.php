<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class YandexDirectNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Store latest company yandex balance.
     *
     * @var \App\Domain\YandexDirect\Models\YandexDirectBalance
     */
    public $latestBalance;

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
     * @param \App\Domain\YandexDirect\Models\YandexDirectBalance $balance
     * @param $email
     */
    public function __construct(Company $company, YandexDirectBalance $balance, $email = '')
    {
        $this->latestBalance = $balance;
        $this->companyInformation = $company;
        $this->email = $email;
        $this->unsubscribeUrl = $email ? EmailManageLink::getUnsubscribeAllUrl($email) : null;
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
        $this->disabledLinkUrl = EmailManageLink::getDisabledLinkUrl($email, 'yandex_direct', $company->id);
        $this->companyInformation->changeEmailNotificationLastSend(EmailNotification::YANDEX_DIRECT_TYPE, $this->email);
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

            return $this->subject('Внимание! Нет получателей уведомления "Яндекс Баланс"')
                ->markdown('emails.notification-error')->to($email);
        }

        return $this->subject('На Яндекс Директе осталось мало средств')
            ->markdown('emails.yandex-direct')->to($this->email);
    }

    public function getNotificationType()
    {
        return EmailNotification::YANDEX_DIRECT_TYPE;
    }

    public function getCompanyId()
    {
        return $this->companyInformation->id;
    }
}
