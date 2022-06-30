<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 21.08.2018
 * Time: 14:03.
 */

namespace App\Domain\ProxyLead\Repositories;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LeadgenChartRepository
{
    /**
     * @var Request
     */
    private $request;

    /**
     * LeadgenChartRepository constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get collection of data.
     *
     * @return Collection
     */
    public function get(): Collection
    {
        switch ($this->getType()) {
            case 'day':
                return $this->daily();
            case 'month':
                return $this->monthly();
            default:
                return $this->daily;
        }
    }

    private function getType()
    {
        return $this->request->has('type') ? $this->request->get('type') : 'day';
    }

    /**
     * Get channel_id from user request.
     *
     * @return mixed
     */
    private function getChannel()
    {
        return $this->request->get('channel_id');
    }

    /**
     * Get start at date.
     *
     * @return Carbon
     */
    private function getStartAtDate() :Carbon
    {
        if (! $this->request->has('start_at')) {
            return now()->startOfMonth();
        }

        return Carbon::parse($this->request->get('start_at'));
    }

    /**
     * Get end at date.
     *
     * @return Carbon
     */
    private function getEndAtDate() :Carbon
    {
        if (! $this->request->has('end_at')) {
            return now()->endOfMonth();
        }

        return Carbon::parse($this->request->get('end_at'));
    }

    /**
     * Create builder with all conditions.
     *
     * @return Builder
     */
    private function getAndGroupBy($groupBy)
    {
        $channel = $this->getChannel();
        $startAt = $this->getStartAtDate();
        $endAt = $this->getEndAtDate();

        $allChannels = User::current()->channels->pluck('id')->toArray();
        $channelsForCounter = [];
        if (! $channel) {
            $channelsForCounter = $allChannels;
        }
        if ($channel && in_array($channel, $allChannels)) {
            $channelsForCounter[] = $channel;
        }

        $params = [
            'account_id' => Account::current()->id,
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'groupBy' => $groupBy,
        ];
        $channels = empty($channelsForCounter) ? '0' : implode(',', $channelsForCounter);
        $results = \DB::select(
            "SELECT DATE_FORMAT(pl.created_at, '%Y-%m-%d') AS for_date, COUNT(pl.id) as target_count
                            FROM pl_report_leads prl
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            JOIN companies ON pls.company_id = companies.id
                            AND companies.account_id = :account_id
                            AND companies.channel_id in ($channels)
                            AND prl.company_confirmed = 1
                            AND prl.created_at BETWEEN :start_at AND :end_at
                            AND pl.deleted_at IS NULL
                            GROUP BY DATE_FORMAT(prl.created_at, :groupBy)",
            $params
        );

        return $results;
    }

    public function daily()
    {
        $queryResults = $this->getAndGroupBy('%Y-%m-%d');

        return collect($queryResults);
    }

    public function monthly()
    {
        $results = $this->getAndGroupBy('%Y-%m');

        return collect($results);
    }
}
