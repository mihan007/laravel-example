<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:15.
 */

namespace App\Domain\Finance;

class EmptyStatusConfiguration extends \App\Support\Status\StatusConfiguration
{
    protected $isEmptyConfiguration = true;

    /**
     * Get approve record from database.
     *
     * @return mixed
     */
    protected function getApproveRecord()
    {
        return null;
    }

    /**
     * Get last reconciliation.
     *
     * @return mixed
     */
    protected function getLastReconciliation()
    {
        return null;
    }
}
