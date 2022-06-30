<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Webpatser\Uuid\Uuid;

class Notifier
{
    /** @var string */
    protected $identifier;

    /**
     * @var Schedule
     */
    private $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->identifier = (string) Uuid::generate(1);
    }

    public function register()
    {
        collect($this->schedule->events())->each(function (Event $event) {
            $commandName = $this->getCommandName($event->command);

            $beforeUrl = route('ping.index').'?'.http_build_query(['name' => $commandName, 'id' => $this->identifier, 'type' => 'start']);
            $afterUrl = route('ping.index').'?'.http_build_query(['name' => $commandName, 'id' => $this->identifier, 'type' => 'finish']);

            $event->pingBefore($beforeUrl)->thenPing($afterUrl);
        });
    }

    private function getCommandName($text): string
    {
        return last(explode(' ', $text));
    }
}
