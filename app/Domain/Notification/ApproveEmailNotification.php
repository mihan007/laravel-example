<?php

namespace App\Domain\Notification;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\ApproveEmail;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApproveEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var EmailNotificationSetting
     */
    private $emailNotificationSetting;

    /**
     * Create a new notification instance.
     *
     * @param EmailNotificationSetting $emailNotificationSetting
     */
    public function __construct(EmailNotificationSetting $emailNotificationSetting)
    {
        $this->emailNotificationSetting = $emailNotificationSetting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param $notifiable
     * @return ApproveEmail
     */
    public function toMail($notifiable)
    {
        return (new ApproveEmail($this->emailNotificationSetting->email))->build();
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->emailNotificationSetting->company;
    }
}
