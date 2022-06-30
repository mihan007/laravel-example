<?php

namespace App\Domain\Roistat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RoistatGoogleAnalytic
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property int $visitCount
 * @property float $visits2leads
 * @property int $leadCount
 * @property float $visitsCost
 * @property float $costPerClick
 * @property float $costPerLead
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RoistatGoogleAnalytic newModelQuery()
 * @method static Builder|RoistatGoogleAnalytic newQuery()
 * @method static Builder|RoistatGoogleAnalytic query()
 * @method static Builder|RoistatGoogleAnalytic whereCostPerClick($value)
 * @method static Builder|RoistatGoogleAnalytic whereCostPerLead($value)
 * @method static Builder|RoistatGoogleAnalytic whereCreatedAt($value)
 * @method static Builder|RoistatGoogleAnalytic whereId($value)
 * @method static Builder|RoistatGoogleAnalytic whereLeadCount($value)
 * @method static Builder|RoistatGoogleAnalytic whereRoistatCompanyConfigId($value)
 * @method static Builder|RoistatGoogleAnalytic whereUpdatedAt($value)
 * @method static Builder|RoistatGoogleAnalytic whereVisitCount($value)
 * @method static Builder|RoistatGoogleAnalytic whereVisits2leads($value)
 * @method static Builder|RoistatGoogleAnalytic whereVisitsCost($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatGoogleAnalyticFactory factory(...$parameters)
 */
class RoistatGoogleAnalytic extends Model
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
    ];

    protected $casts = [
        'visitsCost' => 'double',
    ];
}
