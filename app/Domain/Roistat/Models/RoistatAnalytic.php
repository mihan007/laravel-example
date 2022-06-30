<?php

namespace App\Domain\Roistat\Models;

use App\Domain\Roistat\Observers\RoistatAnalyticObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RoistatAnalytic
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property int $visitCount
 * @property float $visits2leads
 * @property int $leadCount
 * @property float $visitsCost
 * @property float $costPerClick
 * @property float $costPerLead
 * @property string|null $for_date Attach date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RoistatCompanyConfig $roistatCompanyConfig
 * @method static Builder|RoistatAnalytic newModelQuery()
 * @method static Builder|RoistatAnalytic newQuery()
 * @method static Builder|RoistatAnalytic query()
 * @method static Builder|RoistatAnalytic whereCostPerClick($value)
 * @method static Builder|RoistatAnalytic whereCostPerLead($value)
 * @method static Builder|RoistatAnalytic whereCreatedAt($value)
 * @method static Builder|RoistatAnalytic whereForDate($value)
 * @method static Builder|RoistatAnalytic whereId($value)
 * @method static Builder|RoistatAnalytic whereLeadCount($value)
 * @method static Builder|RoistatAnalytic whereRoistatCompanyConfigId($value)
 * @method static Builder|RoistatAnalytic whereUpdatedAt($value)
 * @method static Builder|RoistatAnalytic whereVisitCount($value)
 * @method static Builder|RoistatAnalytic whereVisits2leads($value)
 * @method static Builder|RoistatAnalytic whereVisitsCost($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatAnalyticFactory factory(...$parameters)
 */
class RoistatAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'roistat_company_config_id',
        'visitCount',
        'visits2leads',
        'leadCount',
        'visitsCost',
        'costPerClick',
        'costPerLead',
        'for_date',
    ];

    protected $casts = [
        'roistat_company_config_id' => 'integer',
        'visitCount' => 'integer',
        'visits2leads' => 'double',
        'leadCount' => 'integer',
        'visitsCost' => 'double',
        'costPerClick' => 'double',
        'costPerLead' => 'double',
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(RoistatAnalyticObserver::class);
    }

    public function roistatCompanyConfig()
    {
        return $this->belongsTo(RoistatCompanyConfig::class);
    }
}
