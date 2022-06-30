<?php

namespace App\Domain\Roistat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Roistat\Models\RcBalanceTransaction
 *
 * @property int $id
 * @property int $rc_balance_config_id
 * @property string $date Date of operation
 * @property string $type Type of operation
 * @property string|null $system_name System name of operation
 * @property string|null $display_name
 * @property string|null $project_id Roistat project id
 * @property string $sum Operation amount
 * @property string $balance
 * @property string $virtual_balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RcBalanceTransaction newModelQuery()
 * @method static Builder|RcBalanceTransaction newQuery()
 * @method static Builder|RcBalanceTransaction query()
 * @method static Builder|RcBalanceTransaction whereBalance($value)
 * @method static Builder|RcBalanceTransaction whereCreatedAt($value)
 * @method static Builder|RcBalanceTransaction whereDate($value)
 * @method static Builder|RcBalanceTransaction whereDisplayName($value)
 * @method static Builder|RcBalanceTransaction whereId($value)
 * @method static Builder|RcBalanceTransaction whereProjectId($value)
 * @method static Builder|RcBalanceTransaction whereRcBalanceConfigId($value)
 * @method static Builder|RcBalanceTransaction whereSum($value)
 * @method static Builder|RcBalanceTransaction whereSystemName($value)
 * @method static Builder|RcBalanceTransaction whereType($value)
 * @method static Builder|RcBalanceTransaction whereUpdatedAt($value)
 * @method static Builder|RcBalanceTransaction whereVirtualBalance($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RcBalanceTransactionFactory factory(...$parameters)
 */
class RcBalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'type',
        'system_name',
        'display_name',
        'project_id',
        'sum',
        'balance',
        'virtual_balance',
    ];
}
