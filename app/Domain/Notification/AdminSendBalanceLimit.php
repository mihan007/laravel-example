<?php

namespace App\Domain\Notification;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\AdminBalanceLimitCheck;
use App\Domain\Notification\Mail\UserReportVarification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Mail;

class AdminSendBalanceLimit extends Notification implements ShouldQueue
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
     * @param \App\Domain\Company\Models\Company $company
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
        $emails = $this->company->mainNotifications->pluck('email')->all();

        if (! $emails) {
            $email = $this->company->account->warningEmail;
            Mail::send($this->company)->subject('Внимание! Нет получателей уведомления "Отчет администратора"')
                ->markdown('emails.notification-error')->to($email);
        }

        foreach ($emails as $email) {
            (new AdminBalanceLimitCheck($this->company, $email));
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
        $message = 'Администратору отправлено письмо о приостановке рекламы компании'.$this->company->name;

        return [
            'message' => $message,
            'date' => Carbon::now(),
        ];
    }

    /**
     * @return \App\Domain\Company\Models\Company
     */
    public function getCompany()
    {
        return $this->company;
    }
}
