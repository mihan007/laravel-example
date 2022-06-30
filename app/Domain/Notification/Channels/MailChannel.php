<?php

namespace App\Domain\Notification\Channels;

use App\Domain\Notification\Mail\SubscriberMailMessage;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Message;
use Illuminate\Notifications\Notification;

class MailChannel extends \Illuminate\Notifications\Channels\MailChannel
{
    /**
     * Build the mail message.
     *
     * @param  Message  $mailMessage
     * @param  mixed  $notifiable
     * @param Notification $notification
     * @param  SubscriberMailMessage  $message
     * @return void
     */
    protected function buildMessage($mailMessage, $notifiable, $notification, $message)
    {
        parent::buildMessage($mailMessage, $notifiable, $notification, $message);

        if (isset($message->unsubscribeUrl)) {
            $mailMessage
                ->getHeaders()
                ->addTextHeader('List-Unsubscribe', "<{$message->unsubscribeUrl}>");
        }
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = null;
        if (method_exists($notification, 'getCompany')) {
            $company = $notification->getCompany();
            if ($company && ! empty($company->deleted_at)) {
                return;
            }
        }

        $message = $notification->toMail($notifiable);

        if (! $message) {
            return;
        }

        if (! $notifiable->routeNotificationFor('mail') && ! $message instanceof Mailable) {
            return;
        }

        if ($message instanceof Mailable) {
            return $message->send($this->mailer);
        }

        $this->mailer->send(
            $this->buildView($message),
            $message->data(),
            $this->messageBuilder($notifiable, $notification, $message)
        );
    }
}
