<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Notification\Models\EmailManageLink;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriberMailMessage extends MailMessage
{
    protected $email;

    public $unsubscribeUrl;
    public $notificationSettingsUrl;

    public function __construct($email)
    {
        $this->email = $email;
        $this->unsubscribeUrl = EmailManageLink::getUnsubscribeAllUrl($email);
        $this->notificationSettingsUrl = EmailManageLink::getSettingsUrl($email);
    }

    /**
     * Get the data array for the mail message.
     *
     * @return array
     */
    public function data()
    {
        return array_merge(parent::data(),
            [
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'notificationSettingsUrl' => $this->notificationSettingsUrl,
            ]);
    }
}
