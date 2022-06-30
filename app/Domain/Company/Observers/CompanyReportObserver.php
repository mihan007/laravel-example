<?php

namespace App\Domain\Company\Observers;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;

class CompanyReportObserver
{
    /**
     * Handle the CompanyReport "creating" event.
     *
     * @param CompanyReport $companyReport
     * @return void
     */
    public function creating (CompanyReport $companyReport)
    {
        $this->addAccountToCompanyReport($companyReport);
    }

    private function addAccountToCompanyReport(CompanyReport $companyReport)
    {
        $companyReport->account_id = optional($companyReport->company)->account_id;
    }
}
