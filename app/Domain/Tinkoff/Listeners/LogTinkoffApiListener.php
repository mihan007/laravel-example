<?php

namespace App\Domain\Tinkoff\Listeners;

use App\Domain\Tinkoff\Events\TinkoffApiSent;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LogTinkoffApiListener
{
    public function handle(TinkoffApiSent $event)
    {
        $data = [
            'date' => (new \DateTime())->format(\DateTime::W3C),
            'count_paid' => $event->count_paid,
        ];
        $tinkoffLog = new Logger('tinkoff');
        $tinkoffLog->pushHandler(new StreamHandler(storage_path('logs/tinkoffapi.log')), Logger::INFO);
        $tinkoffLog->info('Start', $data);
    }
}
