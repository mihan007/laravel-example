<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\ApprovedReport
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property string $for_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ApprovedReport newModelQuery()
 * @method static Builder|ApprovedReport newQuery()
 * @method static Builder|ApprovedReport query()
 * @method static Builder|ApprovedReport whereCreatedAt($value)
 * @method static Builder|ApprovedReport whereForDate($value)
 * @method static Builder|ApprovedReport whereId($value)
 * @method static Builder|ApprovedReport whereRoistatCompanyConfigId($value)
 * @method static Builder|ApprovedReport whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\ApprovedReportFactory factory(...$parameters)
 */
class ApprovedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'roistat_company_config_id',
        'for_date',
    ];

    protected $casts = [
        'roistat_company_config_id' => 'integer',
    ];
}
