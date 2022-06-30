<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 28.09.2017
 * Time: 9:23.
 */

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatAnalytic;
use Carbon\Carbon;

class CompanyCostsCounter
{
    /**
     * Store begin of current month.
     *
     * @var Carbon
     */
    protected $beginOfMonth = '';

    /**
     * Store end of current month.
     *
     * @var Carbon
     */
    protected $endOfMonth = '';

    public function __construct()
    {
        $this->beginOfMonth = Carbon::now()->startOfMonth();
        $this->endOfMonth = Carbon::now()->endOfMonth();
    }

    public function make()
    {
        $companies = Company::with('roistatConfig')->get();

        // you can see that we use add day to get analytic information
        // it used because we take analytic for previous days
        // !!!! i think whereBetween uses > < fo checking date period, so we no need to use add date
        $beginAnalyticDate = Carbon::parse($this->beginOfMonth->toDateTimeString())->addDay();
        $endAnalyticDate = Carbon::parse($this->endOfMonth->toDateTimeString())->addDay();

        foreach ($companies as $company) {
            if (empty($company->roistatConfig)) {
                $this->updateCompanyAmountInformation($company, 0);
                continue;
            }

            $monthAnalytics = $company->roistatConfig
                ->analytics()
                ->whereBetween('created_at', [
                    $beginAnalyticDate,
                    $endAnalyticDate,
                ])
                ->get();

            if (empty($monthAnalytics)) {
                $this->updateCompanyAmountInformation($company, 0);
                continue;
            }

            $this->updateCompanyAmountInformation($company, $this->getCompanyAmountOfCosts($monthAnalytics));
        }

        return true;
    }

    /**
     * Get amount of costs for input analytic information.
     *
     * @param RoistatAnalytic $companyAnalyticInformation
     * @return float
     */
    protected function getCompanyAmountOfCosts($companyAnalyticInformation)
    {
        $total = 0.00;

        foreach ($companyAnalyticInformation as $analytic) {
            $total += $analytic->visitsCost;
        }

        return round($total, 2);
    }

    /**
     * Update database information about amount of costs.
     *
     * @param Company $company
     * @param $newAmount
     * @return bool
     */
    protected function updateCompanyAmountInformation(Company $company, $newAmount)
    {
        $totalCosts = $company->totalCosts()->whereBetween('created_at', [
                $this->beginOfMonth,
                $this->endOfMonth,
            ])
            ->first();

        if (empty($totalCosts)) {
            $company->totalCosts()->create(['amount' => $newAmount]);

            return true;
        }

        $totalCosts->amount = $newAmount;
        $totalCosts->save();

        return true;
    }
}
