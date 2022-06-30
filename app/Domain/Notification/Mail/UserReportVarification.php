<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class UserReportVarification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $period;
    /**
     * @var Company
     */
    public $company;

    /**
     * Create a new message instance.
     *
     * @param Company $company
     * @param $period
     * @param $subject
     */
    public function __construct(Company $company, $period, $subject)
    {
        $this->subject = $subject;
        $this->period = $period;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Отчет по лидам за '.$this->subject)
            ->view('emails.user-report');
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
