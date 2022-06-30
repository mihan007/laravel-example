<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 01.11.2018
 * Time: 15:11.
 */

namespace App\Domain\ProxyLead;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Illuminate\Support\Collection;

class RoistatDuplicateChecker extends DuplicateCheckerAbstraction
{
    public function __construct(RoistatProxyLead $lead)
    {
        $this->lead = $lead;
        $this->lead->loadMissing('company.roistatConfig', 'reportLead');
    }

    protected function getDuplicates(): Collection
    {
        $phoneMatchPart = ProxyLead::phoneMeaningPart($this->lead->phone);

        return $this->lead
            ->company
            ->roistatConfig
            ->leads()
            ->where('phone', 'like', '%'.$phoneMatchPart)
            ->where('roistat_proxy_leads.id', '!=', $this->lead->id)
            ->whereNull('deleted_at')
            ->get();
    }

    protected function getTargetDuplicates(Collection $duplicates): Collection
    {
        return $duplicates->filter(function (RoistatProxyLead $lead) {
            return $lead->is_target;
        });
    }

    protected function setDuplicate(Collection $duplicates): void
    {
        $this->lead->reportLead->user_confirmed = RoistatProxyLeadsReport::STATUS_USER_DISAGREE;
        $this->lead->reportLead->admin_confirmed = RoistatProxyLeadsReport::STATUS_ADMIN_AGREE;
        $this->lead->reportLead->deleted = 1;
        $this->lead->reportLead->user_comment = 'Дубль заявки '.$duplicates->last()->roistat;
        $this->lead->reportLead->save();
    }
}
