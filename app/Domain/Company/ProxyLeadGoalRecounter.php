<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 14.08.2018
 * Time: 14:56.
 */

namespace App\Domain\Company;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use Illuminate\Support\Collection;

class ProxyLeadGoalRecounter
{
    private $offset = 0;
    private $limit = 100;

    /**
     * Sync leads.
     *
     * @return bool
     */
    public function sync()
    {
        $leads = $this->getPortionOfLeads();

        while ($leads->count() > 0) {
            $this->countLeads($leads);

            $leads = $this->getPortionOfLeads();
        }

        return true;
    }

    /**
     * Get portion of leads.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    private function getPortionOfLeads()
    {
        $result = ProxyLead::with('reportLead')->offset($this->offset)->limit($this->limit)->get();

        $this->offset += $this->limit;

        return $result;
    }

    /**
     * Count leads.
     *
     * @param Collection $filteredLeads
     * @return Collection
     */
    private function countLeads(Collection $filteredLeads)
    {
        return $filteredLeads->each(function (ProxyLead $lead) {
            $this->countLead($lead);
        });
    }

    /**
     * Increment count for lead.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLead $lead
     * @return ProxyLeadGoalCounter
     */
    private function countLead(ProxyLead $lead)
    {
    }
}
