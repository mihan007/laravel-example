<?php

namespace App\Domain\Notification;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\UserReportVarification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminSendReportVerification extends Notification implements ShouldQueue
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
    public function __construct(Company $company, Carbon $period)
    {
        $this->company = $company;

        if (! $this->company->relationLoaded('emailNotifications')) {
            $this->company->load('emailNotifications');
        }

        $this->period = $period;
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

        if (empty($this->company->reportNotifications) || $this->company->reportNotifications->isEmpty()) {
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
        $months = [
            '',
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь',
        ];

        $subject = $months[$this->period->month].' '.$this->period->year.' года';

        return (new UserReportVarification(
                $this->company,
                (clone $this->period)->startOfMonth()->toDateString(),
                $subject
            ))
            ->to($this->company->reportNotifications->pluck('email')->all());
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if (empty($this->company->reportNotifications) || $this->company->reportNotifications->isEmpty()) {
            $message = 'Администратор не отправил отчет по лидам на согласование  за период ';
            $message .= (clone $this->period)->startOfMonth()->toDateString();
            $message .= ' по '.(clone $this->period)->endOfMonth()->toDateString();
            $message .= ', так как не указан ни один почтовый адрес сотрудника компании.';
        } else {
            $message = 'Администратор отправил отчет по лидам на согласование за период ';
            $message .= (clone $this->period)->startOfMonth()->toDateString();
            $message .= ' по '.(clone $this->period)->endOfMonth()->toDateString();
        }

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
