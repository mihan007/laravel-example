<?php

namespace App\Domain\Roistat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RoistatStatistic
 *
 * @property int $id
 * @property int $company_id
 * @property int $visitCount
 * @property int $leadCount
 * @property int $saleCount
 * @property int $revenue
 * @property int $profit
 * @property int $marketingCosts
 * @property int $salesCosts
 * @property float $cv1
 * @property float $cv2
 * @property float $cpc
 * @property float $cpl
 * @property float $cpo
 * @property int $averageRevenue
 * @property float $roi
 * @property string $for_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RoistatStatistic newModelQuery()
 * @method static Builder|RoistatStatistic newQuery()
 * @method static Builder|RoistatStatistic query()
 * @method static Builder|RoistatStatistic whereAverageRevenue($value)
 * @method static Builder|RoistatStatistic whereCompanyId($value)
 * @method static Builder|RoistatStatistic whereCpc($value)
 * @method static Builder|RoistatStatistic whereCpl($value)
 * @method static Builder|RoistatStatistic whereCpo($value)
 * @method static Builder|RoistatStatistic whereCreatedAt($value)
 * @method static Builder|RoistatStatistic whereCv1($value)
 * @method static Builder|RoistatStatistic whereCv2($value)
 * @method static Builder|RoistatStatistic whereForDate($value)
 * @method static Builder|RoistatStatistic whereId($value)
 * @method static Builder|RoistatStatistic whereLeadCount($value)
 * @method static Builder|RoistatStatistic whereMarketingCosts($value)
 * @method static Builder|RoistatStatistic whereProfit($value)
 * @method static Builder|RoistatStatistic whereRevenue($value)
 * @method static Builder|RoistatStatistic whereRoi($value)
 * @method static Builder|RoistatStatistic whereSaleCount($value)
 * @method static Builder|RoistatStatistic whereSalesCosts($value)
 * @method static Builder|RoistatStatistic whereUpdatedAt($value)
 * @method static Builder|RoistatStatistic whereVisitCount($value)
 * @mixin Eloquent
 */
class RoistatStatistic extends Model
{
    protected $fillable = [
        'company_id',
        'visitCount',
        'leadCount',
        'saleCount',
        'revenue',
        'profit',
        'marketingCosts',
        'salesCosts',
        'cv1',
        'cv2',
        'cpc',
        'cpl',
        'cpo',
        'averageRevenue',
        'roi',
        'for_date',
    ];
}
