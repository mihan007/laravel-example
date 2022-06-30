<?php

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\CheckStatisticsForCompanyForPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckRoistatStatisticsForCompanyForPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roistat:statistic-for-company-for-period {companyId} {start} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store roistat statistic data in database for special period';

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
        $start = Carbon::parse($this->argument('start'));
        $end = Carbon::parse($this->argument('end'));
        $company = Company::find($this->argument('companyId'));

        return (new CheckStatisticsForCompanyForPeriod($start, $end, $company))->check();
    }
}
