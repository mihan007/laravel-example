<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.10.2017
 * Time: 14:37.
 */

namespace App\Console\Commands;

use App\Domain\Company\CompanyRoistatAnalyticUpdater;
use App\Domain\Company\Events\CompaniesAnalyticsReceived;
use App\Domain\Company\Models\Company;

class CompaniesYesterdayRoistatAnalyticsChecker
{
    public function check()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $companyAnalyticUpdater = new CompanyRoistatAnalyticUpdater($company, 'yesterday');
            $companyAnalyticUpdater->update();
        }

        event(new CompaniesAnalyticsReceived());

        return true;
    }
}
