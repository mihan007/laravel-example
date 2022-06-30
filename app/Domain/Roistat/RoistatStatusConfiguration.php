<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:13.
 */

namespace App\Domain\Roistat;

use App\Domain\Roistat\Models\RoistatReconciliation;
use App\Support\Status\StatusConfiguration;

class RoistatStatusConfiguration extends StatusConfiguration
{
    /**
     * Get approve record from database.
     *
     * @return mixed
     */
    protected function getApproveRecord()
    {
        return $this->company->roistatConfig
            ->approvedReports()
            ->where('for_date', $this->period->toDateString())
            ->first();
    }

    /**
     * Get last reconciliation.
     *
     * @return mixed
     */
    protected function getLastReconciliation()
    {
        return RoistatReconciliation::where([
                ['period', $this->period->toDateString()],
                ['roistat_company_config_id', $this->company->roistatConfig->id],
            ])
            ->latest()
            ->first();
    }
}
