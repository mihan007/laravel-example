<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 25.09.2017
 * Time: 14:23.
 */

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Models\TotalDayLead;
use Carbon\Carbon;

class CountRecentLeads
{
    public function __construct()
    {
    }

    public function make()
    {
        $companies = $this->getCompanies();

        if (empty($companies)) {
            return true;
        }

        $amountOfLeads = $this->getAmountOfLeads($companies);

        TotalDayLead::create(['amount' => $amountOfLeads, 'for_date' => Carbon::now()->subDay()->format('Y-m-d')]);

        return true;
    }

    /**
     * Get companies that has active status.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getCompanies()
    {
        return Company::with('roistatConfig.yesterdayAnalytic')->where('check_for_graph', '=', 1)->get();
    }

    /**
     * Get total amount of leads if allCompanies.
     *
     * @param $companies
     * @return int
     */
    protected function getAmountOfLeads($companies)
    {
        $totalAmount = 0;

        foreach ($companies as $company) {
            if (empty($company->roistatConfig)) {
                continue;
            }

            if (empty($company->roistatConfig->yesterdayAnalytic)) {
                continue;
            }

            $totalAmount += $company->roistatConfig->yesterdayAnalytic->leadCount;
        }

        return $totalAmount;
    }
}
