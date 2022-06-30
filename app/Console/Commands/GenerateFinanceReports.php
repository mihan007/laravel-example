<?php

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\FinanceReportCreator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateFinanceReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:generate {--period=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate finance reports for certain month. By default - for previous month.';

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
        $period = empty($this->option('period')) ?
            now()->startOfMonth()->subMonth() :
            Carbon::parse($this->option('period'))->startOfMonth();

        $companies = Company::all();

        foreach ($companies as $company) {
            (new FinanceReportCreator($company, $period))->create();
        }

        return true;
    }
}
