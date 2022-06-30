<?php

namespace App\Domain\Roistat\Models;

use App\Domain\ProxyLead\Models\ReconciliationBase;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RoistatReconciliation
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property string $type Type of reconciliation
 * @property string $period For what period it was created
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RoistatReconciliation newModelQuery()
 * @method static Builder|RoistatReconciliation newQuery()
 * @method static Builder|RoistatReconciliation query()
 * @method static Builder|RoistatReconciliation whereCreatedAt($value)
 * @method static Builder|RoistatReconciliation whereId($value)
 * @method static Builder|RoistatReconciliation wherePeriod($value)
 * @method static Builder|RoistatReconciliation whereRoistatCompanyConfigId($value)
 * @method static Builder|RoistatReconciliation whereType($value)
 * @method static Builder|RoistatReconciliation whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatReconciliationFactory factory(...$parameters)
 */
class RoistatReconciliation extends ReconciliationBase
{
    use HasFactory;

    protected $fillable = [
        'type',
        'period',
    ];

    protected $casts = [
        'roistat_company_config_id' => 'integer',
    ];
}
