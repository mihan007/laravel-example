<?php

namespace App\Domain\Company\Report;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Models\Account;
use App\Models\User;
use App\Support\Reports\ReportBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class EmailableReportBuilder.
 */
class EmailableReportBuilder extends ReportBuilder
{
     /**
     * EmailableReportBuilder constructor.
     * @param null $startAt
     * @param null $endAt
     * @param \App\Models\User|null $currentUser
     * @param \App\Domain\Channel\Models\Channel|null $currentChannel
     * @param \App\Domain\Company\Models\Company|null $currentCompany
     */
    public function __construct(
        $startAt = null,
        $endAt = null,
        $timezone = null
    ) {
        $format = 'Y-m-d H:i:s';
        $dbTimezone = config('app.timezone');
        $appTimezone = $timezone ?? config('app.timezone');

        $tmp = Carbon::createFromFormat($format, $startAt->format($format), $appTimezone)->tz($dbTimezone);
        $this->startAt = Carbon::createFromFormat($format, $tmp->format($format));

        $tmp = Carbon::createFromFormat($format, $endAt->format($format), $appTimezone)->tz($dbTimezone);
        $this->endAt = Carbon::createFromFormat($format, $tmp->format($format));
    }

    /**
     * @return Builder|void
     */
    public function getReportBuilder($proxyLeadSettings, $withTrashed = false)
    {
        $reportBuilder = $this->getProxyLeadBuilderQuery($proxyLeadSettings, $withTrashed);

        return $this->with($reportBuilder);
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param array $reportBuilder
     * @return void
     */
    protected function with($reportBuilder)
    {
        return $reportBuilder->with(['reportLead','reportLead.reason']);
    }

    /**
     * Get proxy leads query.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSettings
     * @param bool $withTrashed
     * @return ProxyLead|Builder|\Illuminate\Database\Query\Builder
     */
    public function getProxyLeadBuilderQuery(ProxyLeadSetting $proxyLeadSettings, $withTrashed = true)
    {
        $leads = ProxyLead::where('proxy_lead_setting_id' , $proxyLeadSettings->id)
                ->period($this->startAt, $this->endAt);
        if ($withTrashed) {
            return $leads->withTrashed();
        }

        return $leads;
    }
}
