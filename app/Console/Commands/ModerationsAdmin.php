<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModerationsAdmin extends Command
{
    protected $signature = 'moderations:send_moderations_admin';

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
        (new ModerationApplicationsAdmin())->sendModerations();
    }
}
