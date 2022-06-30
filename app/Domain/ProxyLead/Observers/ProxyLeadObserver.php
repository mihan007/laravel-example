<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 15.06.2018
 * Time: 16:19.
 */

namespace App\Domain\ProxyLead\Observers;

use App\Domain\Bitrix\BitrixLogTrait;
use App\Domain\Company\Jobs\CompanyReportRebuilder;
use App\Domain\ProxyLead\Jobs\ProxyLeadDuplicateChecker;
use App\Domain\ProxyLead\Models\ProxyLead;

class ProxyLeadObserver
{
    use BitrixLogTrait;

    public function creating(ProxyLead $proxyLead)
    {
        $isPrepaid = optional($proxyLead->company)->prepayment;
        $isFree = optional($proxyLead->company)->free_period;
        $leadCost = optional($proxyLead->company)->lead_cost ?? 0;
        $proxyLead->cost = $isFree || ! $isPrepaid ? 0 : $leadCost;
    }

    public function created(ProxyLead $proxyLead)
    {
        $proxyLead->reportLead()->create([]);
        if ($proxyLead->isTestRoistat()) {
            return;
        }
        ProxyLeadDuplicateChecker::dispatch($proxyLead);
    }

    public function deleted(ProxyLead $proxyLead)
    {
        if ($proxyLead->reportLead) {
            $proxyLead->reportLead->delete();
        }
        if ($proxyLead->company) {
            CompanyReportRebuilder::dispatch($proxyLead->created_at, $proxyLead->created_at, $proxyLead->company);
        }
    }

    public function restored(ProxyLead $proxyLead)
    {
        $proxyLead->load('reportLeadWithTrashed');
        $proxyLead->reportLeadWithTrashed->restore();
        if ($proxyLead->company) {
            CompanyReportRebuilder::dispatch($proxyLead->created_at, $proxyLead->created_at, $proxyLead->company);
        }
    }
}
