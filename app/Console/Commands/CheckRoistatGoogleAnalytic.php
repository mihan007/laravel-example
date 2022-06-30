<?php

namespace App\Console\Commands;

use App\Domain\Roistat\CheckGoogleAnalytic;
use Illuminate\Console\Command;

class CheckRoistatGoogleAnalytic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roistat:googleAnalytic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check roistat google analytic for previous day';

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
        return (new CheckGoogleAnalytic())->check();
    }
}
