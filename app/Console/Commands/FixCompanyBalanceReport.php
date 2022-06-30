<?php

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Observers\CompanyObserver;
use Illuminate\Console\Command;

class FixCompanyBalanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:fix-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach (Company::cursor() as $company) {
            (new CompanyObserver())->updateCompanyReport($company);
        }
    }
}
