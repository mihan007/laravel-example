<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

class ScheduleList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List when scheduled commands are executed.';

    /**
     * @var Schedule
     */
    protected $schedule;

    /**
     * Create a new command instance.
     *
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule)
    {
        parent::__construct();

        $this->schedule = $schedule;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $events = array_map(function ($event) {
            return [
                'cron' => $event->expression,
                'next run at' => $this->getNextRunDate($event),
                'command' => static::fixupCommand($event->command),
                'description' => $event->description,
            ];
        }, $this->schedule->events());

        $this->table(
            ['Cron', 'Next run at', 'Command', 'Description'],
            $events
        );
    }

    /**
     * If it's an artisan command, strip off the PHP.
     *
     * @param $command
     * @return string
     */
    protected static function fixupCommand($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) > 2 && $parts[1] === "'artisan'") {
            array_shift($parts);
        }

        return implode(' ', $parts);
    }

    /**
     * Get the next scheduled run date for this event.
     *
     * @param Event $event
     * @return string
     */
    private function getNextRunDate($event)
    {
        $cron = CronExpression::factory($event->getExpression());
        $date = Carbon::now();
        if ($event->timezone) {
            $date->setTimezone($event->timezone);
        }

        return $cron->getNextRunDate()->format('Y-m-d H:i:s');
    }
}
