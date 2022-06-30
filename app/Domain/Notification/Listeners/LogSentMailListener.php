<?php

namespace App\Domain\Notification\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LogSentMailListener
{
    /**
     * Handle the event.
     *
     * @param MessageSent $event
     * @return void
     * @throws \Exception
     */
    public function handle(MessageSent $event)
    {
        $data = [
            'date' => (new \DateTime())->format(\DateTime::W3C),
            'subject' => $event->message->getSubject(),
            'to' => $event->message->getTo(),
            'from' => $event->message->getFrom(),
            'sender' => $event->message->getFrom(),
            'id' => $event->message->getId(),
        ];
        $emailLog = new Logger('email');
        $emailLog->pushHandler(new StreamHandler(storage_path('logs/email.log')), Logger::INFO);
        $emailLog->info('Email sent', $data);
    }
}
