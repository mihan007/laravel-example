<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompaniesYesterdayAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:analytic-yesterday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update companies analytic for yesterday';

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
        return (new CompaniesYesterdayRoistatAnalyticsChecker())->check();
    }
}
