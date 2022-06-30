<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 20.10.2017
 * Time: 11:33.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Models\TotalDayLead;
use Carbon\Carbon;

class CompaniesTotalLeadsCounter
{
    /**
     * @var Carbon
     */
    private $attachDate = '';

    public function __construct($date = 'yesterday')
    {
        $this->attachDate = Carbon::parse($date);
    }

    public function count()
    {
        $companies = $this->getCompanies();

        if (empty($companies)) {
            return true;
        }

        $amountOfLeads = $this->getAmountOfLeads($companies);

        TotalDayLead::updateOrCreate(['for_date' => $this->attachDate->toDateString()], ['amount' => $amountOfLeads]);

        return true;
    }

    /**
     * Get companies that has active status.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getCompanies()
    {
        return Company::with('roistatConfig')->where('check_for_graph', '=', 1)->get();
    }

    /**
     * Get total amount of leads if allCompanies.
     *
     * @param $companies
     * @return int
     */
    private function getAmountOfLeads($companies)
    {
        $totalAmount = 0;

        foreach ($companies as $company) {
            if (empty($company->roistatConfig)) {
                continue;
            }

            $analytic = new CompanyAnalytic($company);
            $analyticInfo = $analytic->get($this->attachDate->toDateString());

            if (empty($analyticInfo) || empty($analyticInfo['lead_count'])) {
                continue;
            }

            $totalAmount += $analyticInfo['lead_count'];
        }

        return $totalAmount;
    }
}
