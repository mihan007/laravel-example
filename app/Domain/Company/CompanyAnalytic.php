<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 11.10.2017
 * Time: 16:38.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use Carbon\Carbon;

class CompanyAnalytic
{
    /**
     * Company.
     *
     * @var \App\Domain\Company\Models\Company
     */
    private $company;

    public function __construct($company)
    {
        $this->company = $company;
    }

    public function get($date = 'yesterday')
    {
        $forDate = new Carbon($date);

        if (empty($this->company->roistatConfig)) {
            $roistatConfig = $this->company->roistatConfig()->first();

            if (empty($roistatConfig)) {
                return false;
            }
        } else {
            $roistatConfig = $this->company->roistatConfig;
        }

        $analyticForDate = $roistatConfig->analytics()->where('for_date', '=', $forDate->format('Y-m-d'))->first();
        $avitoAnalyticForDate = $roistatConfig->avitoAnalytics()->where('for_date', '=', $forDate->format('Y-m-d'))->first();

        if (empty($analyticForDate) && empty($avitoAnalyticForDate)) {
            return false;
        }

        $result = [
            'visit_count' => 0,
            'visits_to_leads' => 0,
            'lead_count' => 0,
            'visits_cost' => 0,
            'cost_per_click' => 0,
            'cost_per_lead' => 0,
            'for_date' => $forDate->format('Y-m-d'),
        ];

        if (! empty($analyticForDate)) {
            $result['visit_count'] = $result['visit_count'] + $analyticForDate->visitCount;
            $result['visits_to_leads'] = $result['visits_to_leads'] + $analyticForDate->visits2leads;
            $result['lead_count'] = $result['lead_count'] + $analyticForDate->leadCount;
            $result['visits_cost'] = $result['visits_cost'] + $analyticForDate->visitsCost;
            $result['cost_per_click'] = $result['cost_per_click'] + $analyticForDate->costPerClick;
            $result['cost_per_lead'] = $result['cost_per_lead'] + $analyticForDate->costPerLead;
        }

        if (! empty($avitoAnalyticForDate)) {
            $result['visit_count'] = $result['visit_count'] + $avitoAnalyticForDate->visit_count;
            $result['lead_count'] = $result['lead_count'] + $avitoAnalyticForDate->lead_count;

            $result['visits_to_leads'] = $result['visit_count'] ? 100 * $result['lead_count'] / $result['visit_count'] : 0;
        }

        return $result;
    }
}
