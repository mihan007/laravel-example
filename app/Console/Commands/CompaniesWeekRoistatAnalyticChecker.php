<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.10.2017
 * Time: 17:36.
 */

namespace App\Console\Commands;

use App\Domain\Company\CompanyRoistatAnalyticUpdater;
use App\Domain\Company\Events\CompaniesAnalyticsReceived;
use App\Domain\Company\Models\Company;
use Carbon\Carbon;

class CompaniesWeekRoistatAnalyticChecker
{
    public function check()
    {
        $companies = Company::all();

        $start = Carbon::now()->subDay(7);
        $current = clone $start;
        $finish = Carbon::yesterday();

        foreach ($companies as $company) {
            while ($current < $finish) {
                $companyAnalyticUpdater = new CompanyRoistatAnalyticUpdater($company, $current->format('Y-m-d'));
                $companyAnalyticUpdater->update();

                $current->addDay();
            }

            $current = clone $start;
        }

        event(new CompaniesAnalyticsReceived());

        return true;
    }
}
