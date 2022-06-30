<?php

namespace App\Domain\YooMoney\Models;

use App\Domain\YooMoney\Observers\YandexSettingsObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\YooMoney\Models\YandexSetting
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * @property string $wallet_number
 * @property string $secret_key
 * @property string|null $webhook_address
 * @property int|null $is_yandex_wallet
 * @property int|null $is_bank_card
 * @property int $account_id
 * @method static Builder|YandexSetting newModelQuery()
 * @method static Builder|YandexSetting newQuery()
 * @method static Builder|YandexSetting query()
 * @method static Builder|YandexSetting whereAccountId($value)
 * @method static Builder|YandexSetting whereCreatedAt($value)
 * @method static Builder|YandexSetting whereId($value)
 * @method static Builder|YandexSetting whereIsActive($value)
 * @method static Builder|YandexSetting whereIsBankCard($value)
 * @method static Builder|YandexSetting whereIsYandexWallet($value)
 * @method static Builder|YandexSetting whereSecretKey($value)
 * @method static Builder|YandexSetting whereUpdatedAt($value)
 * @method static Builder|YandexSetting whereWalletNumber($value)
 * @method static Builder|YandexSetting whereWebhookAddress($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\YooMoney\Models\YandexSettingFactory factory(...$parameters)
 */
class YandexSetting extends Model
{
    use HasFactory;

    public const SENSITIVE_FIELDS = [
        'wallet_number',
        'secret_key',
    ];

    protected $fillable = [
        'wallet_number',
        'secret_key',
        'is_active',
        'webhook_address',
        'is_yandex_wallet',
        'is_bank_card',
        'account_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(YandexSettingsObserver::class);
    }
}
