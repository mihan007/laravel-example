<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 07.08.2018
 * Time: 15:34.
 */

namespace App\Domain\ProxyLead;

use Illuminate\Database\Eloquent\Collection;

class RoistatTargetCounter extends TargetCounter
{
    /**
     * Return amount of target leads.
     *
     * @param Collection $proxyLeadCollection
     * @return int
     */
    public function getTargetCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($lead) {
            return ! $this->hasReport($lead) || 1 === $lead->reportLead->user_confirmed;
        })->count();
    }

    /**
     * Return amount of non target leads.
     *
     * @param Collection $proxyLeadCollection
     * @return int
     */
    public function getNonTargetCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($lead) {
            return
                $this->hasReport($lead) &&
                0 === $lead->reportLead->user_confirmed &&
                3 === $lead->reportLead->admin_confirmed;
        })->count();
    }

    /**
     * Return amount of not confirmed leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    public function getNotConfirmedCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($lead) {
            return
                $this->hasReport($lead) &&
                0 === $lead->reportLead->user_confirmed &&
                \in_array($lead->reportLead->admin_confirmed, [1, 2], true);
        })->count();
    }

    /**
     * Return amount of not confirmed admin leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    public function getNotConfirmedAdminCount($proxyLeadCollection)
    {
        return 0;
    }

    /**
     * Return amount of not confirmed user leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    public function getNotConfirmedUserCount($proxyLeadCollection)
    {
        return 0;
    }

    /**
     * Check if lead has report.
     *
     * @param $lead
     * @return bool
     */
    private function hasReport($lead)
    {
        return $lead->reportLead !== null;
    }
}
