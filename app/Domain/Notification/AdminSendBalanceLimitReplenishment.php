<?php

namespace App\Domain\Notification;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\UserReportVarification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminSendBalanceLimitReplenishment extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;
    /**
     * @var Carbon
     */
    private $period;

    /**
     * Create a new notification instance.
     *
     * @param Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company)
    {
        $this->company = $company;

        if (! $this->company->relationLoaded('mainNotifications')) {
            $this->company->load('mainNotifications');
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $drivers = ['database'];

        if (empty($this->company->mainNotifications) || $this->company->mainNotifications->isEmpty()) {
            return $drivers;
        }

        $drivers[] = 'mail';

        return $drivers;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return UserReportVarification
     */
    public function toMail($notifiable)
    {
        if ($this->company->mainNotifications->isEmpty()) {
            $email = $this->company->account->warningEmail;
            Mail::send($this->company)->subject('Внимание! Нет получателей уведомления "Отчет администратора"')
                ->markdown('emails.notification-error')->to($email);
        }

        foreach ($this->company->mainNotifications->pluck('email')->all() as $email) {
            (new \App\Domain\Notification\Mail\AdminSendBalanceLimitReplenishment(
                $this->company, $email
            ));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message = 'Администратору отправлено письмо о включении рекламы компании'.$this->company->name;

        return [
            'message' => $message,
            'date' => Carbon::now(),
        ];
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }
}
