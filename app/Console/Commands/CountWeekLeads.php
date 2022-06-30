<?php

namespace App\Console\Commands;

use App\Domain\Company\CompaniesTotalLeadsCounter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CountWeekLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count total leads for each day for previous seven days';

    /**
     * Create a new command instance.
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
        $status = true;
        $startDate = Carbon::yesterday()->subDay(7);
        $endDate = Carbon::yesterday();

        while ($startDate <= $endDate) {
            $counter = new CompaniesTotalLeadsCounter($startDate->toDateString());
            $resultStatus = $counter->count();

            if (! $resultStatus) {
                $status = false;
            }

            $startDate->addDay();
        }

        return $status;
    }
}
