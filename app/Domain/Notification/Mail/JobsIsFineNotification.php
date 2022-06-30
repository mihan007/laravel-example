<?php

namespace App\Domain\Notification\Mail;

use Illuminate\Mail\Mailable;

class JobsIsFineNotification extends Mailable
{
    public $email;
    public $jobs_count;
    /**
     * @var int
     */
    public $repair_time;

    public function __construct($email, $jobs_count, $when_we_reached_max)
    {
        $this->email = $email;
        $this->jobs_count = $jobs_count;
        $this->repair_time = time() - $when_we_reached_max;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Очередь пришла в порядок: '.$this->jobs_count)
            ->markdown('emails.jobs-report')
            ->to($this->email);
    }
}
