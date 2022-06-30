<?php

namespace App\Domain\Company\Observers;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use Carbon\Carbon;

class CompanyObserver
{
    /**
     * @param \App\Domain\Company\Models\Company $company
     */
    public function created(Company $company)
    {
        $this->createCompanyReport($company);
    }

    /**
     * @param \App\Domain\Company\Models\Company $company
     */
    public function updated(Company $company)
    {
        $this->updateCompanyReport($company);
    }

    public function deleted(Company $company)
    {
        $this->softDeleteFromCompanyReport($company);
    }

    public function updateCompanyReport(Company $company)
    {
        CompanyReport::where('company_id', $company->id)
            ->update(
                [
                    'channel_id' => $company->channel_id,
                    'name' => $company->name,
                    'balance' => $company->balance,
                ]
            );
    }

    private function createCompanyReport(Company $company): void
    {
        $startFromTable = CompanyReport::orderBy('report_date', 'asc')
            ->limit(1)
            ->first();
        $start = $startFromTable ?
            Carbon::createFromFormat('Y-m-d', $startFromTable->report_date) :
            Carbon::now()->startOfYear();
        $dataToInsert = [];
        $currentDate = $start;
        $endDate = Carbon::now()->addDay();
        do {
            $dataToInsert[] = $this->getNewCompanyReportRow($currentDate, $company);
            $currentDate = $currentDate->addDay();
        } while ($currentDate <= $endDate);
        CompanyReport::insert($dataToInsert);
    }

    /**
     * @param \DateTime $currentDate
     * @param \App\Domain\Company\Models\Company $company
     * @return array
     */
    private function getNewCompanyReportRow(\DateTime $currentDate, Company $company): array
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

    private function softDeleteFromCompanyReport(Company $company)
    {
        CompanyReport::where('company_id', $company->id)->delete();
    }
}
