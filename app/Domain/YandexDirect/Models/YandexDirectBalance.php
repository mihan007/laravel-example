<?php

namespace App\Domain\YandexDirect\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\YandexDirect\Models\YandexDirectBalance
 *
 * @property int $id
 * @property int $company_id
 * @property int $amount
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @method static Builder|YandexDirectBalance newModelQuery()
 * @method static Builder|YandexDirectBalance newQuery()
 * @method static Builder|YandexDirectBalance query()
 * @method static Builder|YandexDirectBalance whereAmount($value)
 * @method static Builder|YandexDirectBalance whereCompanyId($value)
 * @method static Builder|YandexDirectBalance whereCreatedAt($value)
 * @method static Builder|YandexDirectBalance whereId($value)
 * @method static Builder|YandexDirectBalance whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\YandexDirect\Models\YandexDirectBalanceFactory factory(...$parameters)
 */
class YandexDirectBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
