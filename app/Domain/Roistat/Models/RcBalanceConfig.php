<?php

namespace App\Domain\Roistat\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Domain\Roistat\Models\RcBalanceConfig
 *
 * @property int $id
 * @property int $company_id Panel company id
 * @property string $project_id Roistat project id
 * @property string $api_key Roistat api key
 * @property string $limit_amount Minimum amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|RcBalanceTransaction[] $latestTransaction
 * @property-read int|null $latest_transaction_count
 * @property-read Collection|RcBalanceTransaction[] $transactions
 * @property-read int|null $transactions_count
 * @property-read RcBalanceTransaction|null $yesterdayTransaction
 * @method static Builder|RcBalanceConfig newModelQuery()
 * @method static Builder|RcBalanceConfig newQuery()
 * @method static Builder|RcBalanceConfig query()
 * @method static Builder|RcBalanceConfig whereApiKey($value)
 * @method static Builder|RcBalanceConfig whereCompanyId($value)
 * @method static Builder|RcBalanceConfig whereCreatedAt($value)
 * @method static Builder|RcBalanceConfig whereId($value)
 * @method static Builder|RcBalanceConfig whereLimitAmount($value)
 * @method static Builder|RcBalanceConfig whereProjectId($value)
 * @method static Builder|RcBalanceConfig whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RcBalanceConfigFactory factory(...$parameters)
 */
class RcBalanceConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'api_key',
        'limit_amount',
    ];

    /**
     * Get latest roistat balance transaction.
     *
     * @return mixed
     */
    public function latestTransaction()
    {
        return $this->hasMany(RcBalanceTransaction::class)->orderBy('date', 'desc')->take(1);
    }

    /**
     * Get yesterday roistat balance transaction.
     *
     * @return mixed
     */
    public function yesterdayTransaction()
    {
        return $this->hasOne(RcBalanceTransaction::class)->where('date', '=', Carbon::yesterday()->format('Y-m-d'))->orderBy('date', 'desc');
    }

    /**
     * Configuration ( and company in global ) has many transactions in roistat.
     *
     * @return HasMany
     */
    public function transactions()
    {
        return $this->hasMany(RcBalanceTransaction::class);
    }
}
