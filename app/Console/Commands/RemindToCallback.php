<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

class RemindToCallback extends Command
{
    protected $signature = 'remind-to-callback';

    protected $description = 'Remind company about clients that could not be reached before';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $callbackReminder = new CallbackReminder;
        $callbackReminder->remind();

        return 0;
    }
}
