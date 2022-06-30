<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 14.08.2018
 * Time: 9:31.
 */

namespace App\Domain\Roistat\Observers;

use App\Domain\Roistat\Jobs\RoistatProxyLeadDuplicateChecker;
use App\Domain\Roistat\Models\RoistatProxyLead;

class RoistatProxyLeadObserver
{
    public function created(RoistatProxyLead $lead)
    {
        $lead->loadMissing('company.roistatConfig');

        $data = array_merge($lead->toArray(), ['roistat_company_config_id' => $lead->company->roistatConfig->id]);
        // Seeder crashed when this columns are in result data array
        unset($data['company_id'], $data['roistat_id'], $data['creation_date'], $data['company']);

        $lead->reportLead()->create($data);

        RoistatProxyLeadDuplicateChecker::dispatch($lead);
    }

    private function attachCompanyHasProxyLeadSetting(RoistatProxyLead $lead)
    {
        $lead->loadMissing('company.proxyLeadSettings');

        return $lead->company->proxyLeadSettings !== null;
    }
}
