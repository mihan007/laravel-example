<?php

namespace App\Domain\Tinkoff\Models;

use App\Domain\Tinkoff\Observers\TinkoffSettingsObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Tinkoff\Models\TinkoffSetting
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $is_active
 * @property string $account
 * @property string $token
 * @property int $account_id
 * @property string $inn
 * @method static Builder|TinkoffSetting newModelQuery()
 * @method static Builder|TinkoffSetting newQuery()
 * @method static Builder|TinkoffSetting query()
 * @method static Builder|TinkoffSetting whereAccount($value)
 * @method static Builder|TinkoffSetting whereAccountId($value)
 * @method static Builder|TinkoffSetting whereCreatedAt($value)
 * @method static Builder|TinkoffSetting whereId($value)
 * @method static Builder|TinkoffSetting whereInn($value)
 * @method static Builder|TinkoffSetting whereIsActive($value)
 * @method static Builder|TinkoffSetting whereToken($value)
 * @method static Builder|TinkoffSetting whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TinkoffSetting extends Model
{
    public const SENSITIVE_FIELDS = [
        'account',
        'token',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'is_active',
        'account',
        'token',
        'account_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(TinkoffSettingsObserver::class);
    }
}
