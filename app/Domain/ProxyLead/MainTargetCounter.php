<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.06.2018
 * Time: 15:48.
 */

namespace App\Domain\ProxyLead;

class MainTargetCounter extends TargetCounter
{
    /**
     * Return amount of target leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    public function getTargetCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($item) {
            return $item->reportLead ? $item->reportLead->is_target && $item->reportLead->deleted_at === null : false;
        })
            ->count();
    }

    /**
     * Return amount of non target leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    public function getNonTargetCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($item) {
            return $item->reportLead->is_non_targeted && $item->reportLead->deleted_at === null;
        })
            ->count();
    }

    /**
     * Return amount of not confirmed leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    public function getNotConfirmedCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($item) {
            return $item->reportLead->is_not_confirmed;
        })
            ->count();
    }

    /**
     * Return amount of not confirmed admin leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    public function getNotConfirmedAdminCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($item) {
            return $item->reportLead->is_not_confirmed_admin;
        })
            ->count();
    }

    /**
     * Return amount of not confirmed user leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    public function getNotConfirmedUserCount($proxyLeadCollection)
    {
        return $proxyLeadCollection->filter(function ($item) {
            return $item->reportLead->is_not_confirmed_user;
        })
            ->count();
    }
}
