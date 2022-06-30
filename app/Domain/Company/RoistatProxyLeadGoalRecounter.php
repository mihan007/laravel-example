<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 14.08.2018
 * Time: 11:24.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Illuminate\Support\Collection;

/**
 * Class RoistatProxyLeadGoalRecounter.
 *
 * This class was created for recount all roistat proxy leads goal counts
 * You MUST use it only ONCE
 */
class RoistatProxyLeadGoalRecounter
{
    private $offset = 0;
    private $limit = 100;

    private $skipCompanies = [];

    public function __construct()
    {
        $this->skipCompanies = $this->getCompaniesThatHasProxyLeadSetting();
    }

    /**
     * Sync leads.
     *
     * @return bool
     */
    public function sync()
    {
        $leads = $this->getPortionOfLeada();

        while ($leads->count() > 0) {
            $filteredLeads = $this->filterLeads($leads);

            if ($filteredLeads->count() !== 0) {
                $this->countLeads($filteredLeads);
            }

            $leads = $this->getPortionOfLeada();
        }

        return true;
    }

    private function getCompaniesThatHasProxyLeadSetting(): Collection
    {
        return Company::has('proxyLeadSettings')->get();
    }

    /**
     * Get portion of leads.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    private function getPortionOfLeada()
    {
        $result = RoistatProxyLead::with('reportLead')->offset($this->offset)->limit($this->limit)->get();

        $this->offset += $this->limit;

        return $result;
    }

    /**
     * Count leads.
     *
     * @param Collection $filteredLeads
     * @return $this
     */
    private function countLeads(Collection $filteredLeads)
    {
        return $filteredLeads->each(function (RoistatProxyLead $lead) {
            $this->countLead($lead);
        });
    }

    /**
     * Increment count for lead.
     *
     * @param \App\Domain\Roistat\Models\RoistatProxyLead $lead
     * @return \App\Domain\ProxyLead\Models\ProxyLeadGoalCounter
     */
    private function countLead(RoistatProxyLead $lead)
    {
    }

    /**
     * Filter leads by rules.
     *
     * @param $leads
     * @return mixed
     */
    private function filterLeads(Collection $leads)
    {
        // take only leads that have report
        // and report leads are not deleted
        $filteredLeads = $leads->filter(function (RoistatProxyLead $lead) {
            return $lead->reportLead !== null && ! $lead->reportLead->deleted;
        })
        ->filter(function (RoistatProxyLead $lead) {
            return ! \in_array($lead->company_id, $this->skipCompanies->pluck('id')->all(), true);
        });

        return $filteredLeads;
    }
}
