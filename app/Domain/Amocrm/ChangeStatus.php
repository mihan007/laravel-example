<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 16.05.2018
 * Time: 14:53.
 */

namespace App\Domain\Amocrm;

use App\Domain\Amocrm\Models\AmocrmLead;
use App\Domain\Amocrm\Models\CompanyAmocrmConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ChangeStatus
{
    /**
     * @var \App\Domain\Amocrm\Models\CompanyAmocrmConfig
     */
    private $config;

    /** @var array */
    private $leadInfo;

    /**
     * ChangeStatus constructor.
     * @param \App\Domain\Amocrm\Models\CompanyAmocrmConfig $config
     * @param $leadInfo
     */
    public function __construct(CompanyAmocrmConfig $config, $leadInfo)
    {
        $this->config = $config;
        $this->leadInfo = $leadInfo;
    }

    /**
     * Change status information of the lead.
     *
     * @return bool
     */
    public function change()
    {
        /** @var Collection $configStatuses */
        $configStatuses = $this->config->statuses()->get();

        $statusId = $this->leadInfo['status_id'];

        $index = $configStatuses->search(function ($item, $key) use ($statusId) {
            return $item->status_id == $statusId;
        });

        $data['target_set_at'] = null;

        if ($index !== false) {
            $attachStatus = $configStatuses[$index];

            if ('target' === $attachStatus->type) {
                $data['target_set_at'] = Carbon::now()->toDateTimeString();
            }
        }

        /** @var \App\Domain\Amocrm\Models\AmocrmLead $lead */
        $lead = AmocrmLead::where([
            ['company_amocrm_config_id', '=', $this->config->id],
            ['lead_id', '=', $this->leadInfo['id']],
        ])->first();

        if (null === $lead) {
            return false;
        }

        $lead->status_id = $this->leadInfo['status_id'];
        $lead->old_status_id = $this->leadInfo['old_status_id'];
        $lead->last_modified = Carbon::createFromTimestamp($this->leadInfo['last_modified'])->toDateTimeString();
        $lead->target_set_at = $data['target_set_at'];

        $lead->save();

        return true;
    }
}
