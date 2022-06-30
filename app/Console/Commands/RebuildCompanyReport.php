<?php

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RebuildCompanyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company-report:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild company report since start of the year';

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
        CompanyReport::query()->truncate();

        $start = Carbon::now()->startOfYear()->startOfDay();
        $end = Carbon::now()->endOfDay();
        while ($end > $start) {
            $startOfReport = clone $start;
            if ($start->month !== $end->month) {
                $endOfReport = (clone $start)->endOfMonth()->endOfDay();
            } else {
                $endOfReport = clone $end;
            }
            $this->prepareEmpty($startOfReport, $endOfReport);
            $startFormatted = $startOfReport->toDateString();
            $endFormatted = $endOfReport->toDateString();
            echo "Build report for {$startFormatted} - {$endFormatted}\n";
            Artisan::call('report:build', [
                'startDate' => $startFormatted,
                'endDate' => $endFormatted,
            ]);
            $start = $start->addMonth()->startOfMonth()->startOfDay();
        }
    }

    public function prepareEmpty($start, $endAt): void
    {
        foreach (Company::cursor() as $company) {
            $dataToInsert = [];
            $currentDate = clone $start;
            do {
                $dataToInsert[] = $this->getNewCompanyReportRow($currentDate, $company);
                $currentDate = $currentDate->addDay();
            } while ($currentDate <= $endAt);
            CompanyReport::insert($dataToInsert);
        }
    }

    /**
     * @param DateTime $currentDate
     * @param \App\Domain\Company\Models\Company $company
     * @return array
     */
    private function getNewCompanyReportRow(DateTime $currentDate, Company $company): array
    {
        $insert = [
            'report_date' => $currentDate->format('Y-m-d'),
            'company_id' => $company->id,
            'channel_id' => $company->channel_id,
            'name' => $company->name,
            'amount' => 0,
            'balance' => 0,
            'target_leads' => 0,
            'target_profit' => 0,
            'target_percent' => 0,
            'cpl' => 0,
            'costs' => 0,
            'yandex_status' => 'not_configured',
            'google_status' => 'not_configured',
            'roistat_status' => 'not_configured',
            'start_at' => $currentDate->startOfDay()->toDateTimeString(),
            'end_at' => $currentDate->endOfDay()->toDateTimeString(),
            'target_all' => 0,
        ];

        return $insert;
    }
}
