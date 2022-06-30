<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 18.09.2018
 * Time: 17:08.
 */

namespace App\Domain\ProxyLead;

use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Illuminate\Support\Collection;

class DuplicateChecker extends DuplicateCheckerAbstraction
{
    /** @var ReasonsOfRejection */
    protected $reasonOfRejection;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
     */
    protected $lead;

    public function __construct(ProxyLead $lead)
    {
        $this->lead = $lead;
        $this->lead->loadMissing('reportLead', 'proxyLeadSetting.company.channel');
        $this->reasonOfRejection = ReasonsOfRejection::firstOrCreate(
            [
                'name' => 'Дубль заявки',
            ]
        );
    }

    protected function getDuplicates(): Collection
    {
        $phoneMatchPart = ProxyLead::phoneMeaningPart($this->lead->phone);

        return $this->lead
            ->proxyLeadSetting
            ->proxyLeads()
            ->where('phone', 'like', '%'.$phoneMatchPart)
            ->where('id', '<', $this->lead->id)
            ->whereNull('deleted_at')
            ->get();
    }

    protected function getTargetDuplicates(Collection $duplicates): Collection
    {
        return $duplicates->filter(
            function (ProxyLead $lead, $key) {
                return $lead->is_target;
            }
        );
    }

    protected function setDuplicate(Collection $duplicates): void
    {
        $this->lead->reportLead->company_confirmed = PlReportLead::STATUS_DOUBLE_APPLICATION;
        $this->lead->reportLead->admin_confirmed = PlReportLead::STATUS_AGREE;
        $this->lead->reportLead->reasons_of_rejection_id = $this->reasonOfRejection->id;
        $this->lead->reportLead->company_comment = $duplicates->first()->id;
        $this->lead->reportLead->save();
    }
}
