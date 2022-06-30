<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 11.10.2017
 * Time: 10:23.
 */

namespace App\Console\Commands;

use App\Domain\Company\CompanyRoistatAvitoAnalyticUpdater;
use App\Domain\Company\Events\CompaniesAnalyticsReceived;
use App\Domain\Company\Models\Company;

class CompaniesAvitoAnalyticsChecker
{
    public function check()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $companyAnalyticUpdater = new CompanyRoistatAvitoAnalyticUpdater($company, 'yesterday');
            $companyAnalyticUpdater->update();
        }

        event(new CompaniesAnalyticsReceived());

        return true;
    }
}
