<?php

namespace App\Domain\Company\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Observers\CompanyObserver;
use App\Domain\Company\Observers\CompanyReportObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Company\Models\CompanyReport
 *
 * @property int $id
 * @property string|null $report_date
 * @property int $company_id
 * @property int|null $channel_id
 * @property string $name
 * @property int $amount
 * @property int $balance
 * @property int $target_leads
 * @property int $not_confirmed_leads
 * @property int $target_profit
 * @property float $target_percent
 * @property float $cpl
 * @property int $costs
 * @property string $yandex_status
 * @property string $google_status
 * @property string $roistat_status
 * @property string $start_at
 * @property string $end_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $target_all
 * @method static Builder|CompanyReport newModelQuery()
 * @method static Builder|CompanyReport newQuery()
 * @method static Builder|CompanyReport query()
 * @method static Builder|CompanyReport whereAmount($value)
 * @method static Builder|CompanyReport whereBalance($value)
 * @method static Builder|CompanyReport whereChannelId($value)
 * @method static Builder|CompanyReport whereCompanyId($value)
 * @method static Builder|CompanyReport whereCosts($value)
 * @method static Builder|CompanyReport whereCpl($value)
 * @method static Builder|CompanyReport whereCreatedAt($value)
 * @method static Builder|CompanyReport whereEndAt($value)
 * @method static Builder|CompanyReport whereGoogleStatus($value)
 * @method static Builder|CompanyReport whereId($value)
 * @method static Builder|CompanyReport whereName($value)
 * @method static Builder|CompanyReport whereReportDate($value)
 * @method static Builder|CompanyReport whereRoistatStatus($value)
 * @method static Builder|CompanyReport whereStartAt($value)
 * @method static Builder|CompanyReport whereTargetAll($value)
 * @method static Builder|CompanyReport whereTargetLeads($value)
 * @method static Builder|CompanyReport whereTargetPercent($value)
 * @method static Builder|CompanyReport whereTargetProfit($value)
 * @method static Builder|CompanyReport whereUpdatedAt($value)
 * @method static Builder|CompanyReport whereYandexStatus($value)
 * @mixin Eloquent
 * @method static Builder|CompanyReport whereNotConfirmedLeads($value)
 */
class CompanyReport extends Model
{
    use HasTimestamps;

    public $table = 'company_report';

    public $fillable = [
        'account_id',
        'company_id',
        'channel_id',
        'name',
        'amount',
        'balance',
        'target_leads',
        'target_percent',
        'target_profit',
        'cpl',
        'costs',
        'start_at',
        'end_at',
        'yandex_status',
        'google_status',
        'roistat_status',
        'report_date',
        'target_all',
        'not_confirmed_leads',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function boot()
    {
        parent::boot();
        self::observe(CompanyReportObserver::class);
    }
}
