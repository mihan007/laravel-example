<?php

namespace App\Domain\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AccountAlertsNotification extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;

    /**
     * @var
     */
    public $currentRoistatBalance;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Не распознан входящий платеж Тинькофф')
            ->markdown('emails.account-alert');
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
