<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.06.2018
 * Time: 15:48.
 */

namespace App\Domain\ProxyLead;

abstract class TargetCounter
{
    /**
     * Return amount of target leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    abstract public function getTargetCount($proxyLeadCollection);

    /**
     * Return amount of non target leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    abstract public function getNonTargetCount($proxyLeadCollection);

    /**
     * Return amount of not confirmed leads.
     *
     * @param $proxyLeadCollection
     * @return int
     */
    abstract public function getNotConfirmedCount($proxyLeadCollection);

    /**
     * Return amount of not confirmed admin leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    abstract public function getNotConfirmedAdminCount($proxyLeadCollection);

    /**
     * Return amount of not confirmed user leads.
     *
     * @param $proxyLeadCollection
     * @return mixed
     */
    abstract public function getNotConfirmedUserCount($proxyLeadCollection);
}
