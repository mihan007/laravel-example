<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ApproveEmail extends SubscriberMailMessage implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $email;

    /**
     * ApproveEmail constructor.
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
        parent::__construct($email);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Подтвердите уведомления на ваш ящик')
            ->markdown('emails.md.subscription.approve', [
                'url' => EmailManageLink::getSubscribePendingUrl($this->email),
                'notificationTypes' => EmailNotificationSetting::getPendingNotificationList($this->email),
            ]);
    }
}
