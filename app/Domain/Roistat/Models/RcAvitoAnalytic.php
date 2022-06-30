<?php

namespace App\Domain\Roistat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RcAvitoAnalytic
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property int $visit_count
 * @property float $visits_to_leads
 * @property int $lead_count
 * @property float $visits_cost
 * @property float $cost_per_click
 * @property float $cost_per_lead
 * @property string|null $for_date Attach date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RcAvitoAnalytic newModelQuery()
 * @method static Builder|RcAvitoAnalytic newQuery()
 * @method static Builder|RcAvitoAnalytic query()
 * @method static Builder|RcAvitoAnalytic whereCostPerClick($value)
 * @method static Builder|RcAvitoAnalytic whereCostPerLead($value)
 * @method static Builder|RcAvitoAnalytic whereCreatedAt($value)
 * @method static Builder|RcAvitoAnalytic whereForDate($value)
 * @method static Builder|RcAvitoAnalytic whereId($value)
 * @method static Builder|RcAvitoAnalytic whereLeadCount($value)
 * @method static Builder|RcAvitoAnalytic whereRoistatCompanyConfigId($value)
 * @method static Builder|RcAvitoAnalytic whereUpdatedAt($value)
 * @method static Builder|RcAvitoAnalytic whereVisitCount($value)
 * @method static Builder|RcAvitoAnalytic whereVisitsCost($value)
 * @method static Builder|RcAvitoAnalytic whereVisitsToLeads($value)
 * @mixin Eloquent
 */
class RcAvitoAnalytic extends Model
{
    protected $fillable = [
        'visit_count',
        'visits_to_leads',
        'lead_count',
        'visits_cost',
        'cost_per_click',
        'cost_per_lead',
        'for_date',
    ];
}
