<?php

namespace App\Domain\Roistat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RoistatAnalyticsDimensionValue
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property string $title
 * @property string $value
 * @property int $is_active Set active status for mian analytic dimension
 * @property int $is_google_active Status for activate roistat google analytics
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RoistatCompanyConfig $roistatConfig
 * @method static Builder|RoistatAnalyticsDimensionValue newModelQuery()
 * @method static Builder|RoistatAnalyticsDimensionValue newQuery()
 * @method static Builder|RoistatAnalyticsDimensionValue query()
 * @method static Builder|RoistatAnalyticsDimensionValue whereCreatedAt($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereId($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereIsActive($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereIsGoogleActive($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereRoistatCompanyConfigId($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereTitle($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereUpdatedAt($value)
 * @method static Builder|RoistatAnalyticsDimensionValue whereValue($value)
 * @mixin Eloquent
 */
class RoistatAnalyticsDimensionValue extends Model
{
    protected $fillable = [
        'title',
        'value',
        'roistat_company_config_id',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function roistatConfig()
    {
        return $this->belongsTo(\App\Domain\Roistat\Models\RoistatCompanyConfig::class);
    }
}
