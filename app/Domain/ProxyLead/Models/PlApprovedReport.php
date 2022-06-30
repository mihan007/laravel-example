<?php

namespace App\Domain\ProxyLead\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\PlApprovedReport
 *
 * @property int $id
 * @property int $proxy_lead_setting_id
 * @property string $for_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PlApprovedReport newModelQuery()
 * @method static Builder|PlApprovedReport newQuery()
 * @method static Builder|PlApprovedReport query()
 * @method static Builder|PlApprovedReport whereCreatedAt($value)
 * @method static Builder|PlApprovedReport whereForDate($value)
 * @method static Builder|PlApprovedReport whereId($value)
 * @method static Builder|PlApprovedReport whereProxyLeadSettingId($value)
 * @method static Builder|PlApprovedReport whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\PlApprovedReportFactory factory(...$parameters)
 */
class PlApprovedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'proxy_lead_setting_id',
        'for_date',
    ];
}
