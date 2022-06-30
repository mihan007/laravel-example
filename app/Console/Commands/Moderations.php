<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Moderations extends Command
{
    protected $signature = 'moderations:send_moderations';

    protected $description = 'Модерация заявок';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return (new ModerationApplications())->sendModerations();
    }
}
