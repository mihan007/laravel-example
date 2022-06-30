<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:12.
 */

namespace App\Domain\ProxyLead;

use App\Domain\ProxyLead\Models\Reconclication;
use App\Support\Status\StatusConfiguration;

class ProxyLeadStatusConfiguration extends StatusConfiguration
{
    /**
     * Get approve record from database.
     *
     * @return mixed
     */
    protected function getApproveRecord()
    {
        return $this->company->proxyLeadSettings
            ->approvedReports()
            ->where('for_date', $this->period->toDateString())->first();
    }

    /**
     * Get last reconciliation.
     *
     * @return mixed
     */
    protected function getLastReconciliation()
    {
        return Reconclication::where([
                ['period', $this->period->toDateString()],
                ['proxy_lead_setting_id', $this->company->proxyLeadSettings->id],
            ])
            ->latest()
            ->first();
    }
}
