<?php

namespace App\Domain\ProxyLead\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\Reconclication
 *
 * @property int $id
 * @property int $proxy_lead_setting_id
 * @property string $type Type of the reconclication sender
 * @property string $period Reconclication period
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Reconclication newModelQuery()
 * @method static Builder|Reconclication newQuery()
 * @method static Builder|Reconclication query()
 * @method static Builder|Reconclication whereCreatedAt($value)
 * @method static Builder|Reconclication whereId($value)
 * @method static Builder|Reconclication wherePeriod($value)
 * @method static Builder|Reconclication whereProxyLeadSettingId($value)
 * @method static Builder|Reconclication whereType($value)
 * @method static Builder|Reconclication whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\ReconclicationFactory factory(...$parameters)
 */
class Reconclication extends ReconciliationBase
{
    use HasFactory;

    protected $fillable = [
        'proxy_lead_setting_id',
        'type',
        'period',
    ];

    protected $casts = [
        'proxy_lead_setting_id' => 'integer',
    ];
}
