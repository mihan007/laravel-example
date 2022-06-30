<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 16.05.2018
 * Time: 14:44.
 */

namespace App\Domain\Amocrm;

use App\Domain\Amocrm\Models\AmocrmLead;
use App\Domain\Amocrm\Models\CompanyAmocrmConfig;
use Carbon\Carbon;

class NewLead
{
    /**
     * @var \App\Domain\Amocrm\Models\CompanyAmocrmConfig
     */
    private $config;

    /** @var array */
    private $leadInfo;

    /**
     * NewLead constructor.
     * @param \App\Domain\Amocrm\Models\CompanyAmocrmConfig $config
     * @param $leadInfo
     */
    public function __construct(CompanyAmocrmConfig $config, $leadInfo)
    {
        $this->config = $config;
        $this->leadInfo = $leadInfo;
    }

    /**
     * Add new lead.
     *
     * @return mixed
     */
    public function add()
    {
        $entryStatuses = $this->config->statuses()->entry()->get();
        $statusId = $this->leadInfo['status_id'];

        $index = $entryStatuses->search(function ($item, $key) use ($statusId) {
            return $item->status_id == $statusId;
        });

        if (false === $index) {
            return false;
        }

        $data = $this->leadInfo;
        $data['name'] = empty($data['name']) ? '' : $data['name'];
        $data['lead_id'] = $data['id'];
        $data['company_amocrm_config_id'] = $this->config->id;
        $data['last_modified'] = Carbon::createFromTimestamp($data['last_modified'])->toDateTimeString();
        $data['date_create'] = Carbon::createFromTimestamp($data['date_create'])->toDateTimeString();

        return AmocrmLead::create($data);
    }
}
