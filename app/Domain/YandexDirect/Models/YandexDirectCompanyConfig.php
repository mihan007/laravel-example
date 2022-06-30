<?php

namespace App\Domain\YandexDirect\Models;

use App\Domain\Company\Models\Company;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\YandexDirect\Models\YandexDirectCompanyConfig
 *
 * @property int $id
 * @property int $company_id
 * @property string $yandex_auth_key
 * @property string $yandex_login
 * @property string $amount
 * @property int $token_life_time
 * @property string|null $token_added_on
 * @property string|null $limit_amount Minimum amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @method static Builder|YandexDirectCompanyConfig newModelQuery()
 * @method static Builder|YandexDirectCompanyConfig newQuery()
 * @method static Builder|YandexDirectCompanyConfig query()
 * @method static Builder|YandexDirectCompanyConfig whereAmount($value)
 * @method static Builder|YandexDirectCompanyConfig whereCompanyId($value)
 * @method static Builder|YandexDirectCompanyConfig whereCreatedAt($value)
 * @method static Builder|YandexDirectCompanyConfig whereId($value)
 * @method static Builder|YandexDirectCompanyConfig whereLimitAmount($value)
 * @method static Builder|YandexDirectCompanyConfig whereTokenAddedOn($value)
 * @method static Builder|YandexDirectCompanyConfig whereTokenLifeTime($value)
 * @method static Builder|YandexDirectCompanyConfig whereUpdatedAt($value)
 * @method static Builder|YandexDirectCompanyConfig whereYandexAuthKey($value)
 * @method static Builder|YandexDirectCompanyConfig whereYandexLogin($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\YandexDirect\Models\YandexDirectCompanyConfigFactory factory(...$parameters)
 */
class YandexDirectCompanyConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'yandex_auth_key', 'yandex_login', 'amount', 'limit_amount',
    ];

    /**
     * Get the company that owns the Yandex Direct Config.
     */
    public function company()
    {
        return $this->belongsTo(\App\Domain\Company\Models\Company::class);
    }
}
