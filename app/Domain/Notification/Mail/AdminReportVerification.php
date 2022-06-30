<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Company\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AdminReportVerification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var Company
     */
    public $company;
    public $period;

    /**
     * Create a new message instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param $period
     * @param $subject
     */
    public function __construct(Company $company, $period, $subject)
    {
        $this->company = $company;
        $this->period = $period;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Отчет по лидам за '.$this->subject)
            ->view('emails.admin-report');
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return true;
    }
}
