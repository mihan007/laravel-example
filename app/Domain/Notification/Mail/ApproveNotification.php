<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ApproveNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var Company
     */
    public $company;
    /**
     * @var Carbon
     */
    public $period;

    /** @var string */
    public $readableMonth;

    /**
     * Create a new message instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company, Carbon $period)
    {
        $this->company = $company;
        $this->company->loadMissing('proxyLeadSettings', 'roistatConfig');

        $this->period = $period;
        $this->readableMonth = $this->getReadableMonth($period->month);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Неоходимо согласовать отчет за период с '.
            (clone $this->period)->toDateString().' по '.(clone $this->period)->endOfMonth()->toDateString();

        return $this->subject($subject)
            ->markdown('emails.report.approve-notification');
    }

    private function getReadableMonth($numberOfMonth)
    {
        $months = ['', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];

        return $months[$numberOfMonth];
    }

    public function getNotificationType()
    {
        return EmailNotification::REPORT_TYPE;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }
}
