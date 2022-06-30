<?php

namespace App\Domain\Account\Models;

use App\Domain\Account\Observers\AccountSettingsObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Account\Models\AccountSetting
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $is_active
 * @property string|null $bank
 * @property string|null $bik
 * @property string|null $k_account
 * @property string|null $r_account
 * @property int|null $account_id
 * @method static Builder|AccountSetting newModelQuery()
 * @method static Builder|AccountSetting newQuery()
 * @method static Builder|AccountSetting query()
 * @method static Builder|AccountSetting whereAccountId($value)
 * @method static Builder|AccountSetting whereBank($value)
 * @method static Builder|AccountSetting whereBik($value)
 * @method static Builder|AccountSetting whereCreatedAt($value)
 * @method static Builder|AccountSetting whereId($value)
 * @method static Builder|AccountSetting whereIsActive($value)
 * @method static Builder|AccountSetting whereKAccount($value)
 * @method static Builder|AccountSetting whereRAccount($value)
 * @method static Builder|AccountSetting whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AccountSetting extends Model
{
    public const SENSITIVE_FIELDS = [
        'bik',
        'k_account',
        'r_account',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'is_active',
        'bank',
        'bik',
        'k_account',
        'r_account',
        'account_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(AccountSettingsObserver::class);
    }
}
