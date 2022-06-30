<?php

namespace App\Domain\Notification;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\AdminReportVerification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserSendReportVerification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return AdminReportVerification
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

        $email = $this->company->account->warningEmail;

        return (new AdminReportVerification(
                    $this->company,
                    (clone $this->period)->startOfMonth()->toDateString(),
                    $subject
                ))
                ->to($email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message = 'Сотрудник компании отправил отчет по лидам на согласование за период ';
        $message .= (clone $this->period)->startOfMonth()->toDateString();
        $message .= ' по '.(clone $this->period)->endOfMonth()->toDateString();

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
