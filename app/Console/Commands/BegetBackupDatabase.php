<?php

namespace App\Console\Commands;

use App\Domain\Beget\BackupApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class BegetBackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beget:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Beget backup database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        (new BackupApi(Config::get('beget.username'), Config::get('beget.password')))->downloadMysql(['zametk1o_admpnl']);
    }
}
