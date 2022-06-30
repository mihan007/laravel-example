<?php

namespace App\Domain\ProxyLead\Observers;

use App\Domain\Company\Jobs\CompanyReportRebuilder;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;

class PlReportLeadObserver
{
    public function updated(PlReportLead $plReportLead)
    {
        $proxyLead = $plReportLead->proxyLead;
        if (!$proxyLead) {
            return;
        }
        if ($proxyLead->isTestRoistat()) {
            return;
        }
        if ($proxyLead->company) {
            CompanyReportRebuilder::dispatch($proxyLead->created_at, $proxyLead->created_at, $proxyLead->company);
        }

        $reportLead = $plReportLead;
        $companyConfirmed = $reportLead->company_confirmed;
        $notBeforeCalledCounter = $reportLead->not_before_called_counter;
        $adminConfirmed = $reportLead->admin_confirmed;
        $status = 0;

        if ($adminConfirmed == 1) {
            if ($companyConfirmed === 5 && $notBeforeCalledCounter < 4) {
                $status = 1;
            } elseif ($companyConfirmed === 1) {
                $status = 1;
            } else {
                $status = 1;
            }
        }

        if ($adminConfirmed == 2) {
            $status = 2;
        }

        if ($adminConfirmed == 0) {
            if ($companyConfirmed === 1) {
                $status = 0;
            } else {
                $status = 2;
            }
        }

        ProxyLead::where('id', $reportLead->proxy_lead_id)->update(
            [
                'status' => $status
            ]
        );
    }
}
