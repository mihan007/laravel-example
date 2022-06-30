<?php

namespace App\Domain\Notification\Mail;

use Illuminate\Mail\Mailable;

class JobsReachedLimitNotification extends Mailable
{
    public $email;
    public $jobs_count;

    public function __construct($email, $jobs_count)
    {
        $this->email = $email;
        $this->jobs_count = $jobs_count;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Очередь переполнена: '.$this->jobs_count)
            ->markdown('emails.jobs-report')->to($this->email);
    }
}
