<?php

namespace App\Domain\Zadarma\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Zadarma\Models\ZadarmaCompanyConfig
 *
 * @property int $id
 * @property int $company_id
 * @property string $key
 * @property string $secret
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ZadarmaCompanyConfig newModelQuery()
 * @method static Builder|ZadarmaCompanyConfig newQuery()
 * @method static Builder|ZadarmaCompanyConfig query()
 * @method static Builder|ZadarmaCompanyConfig whereCompanyId($value)
 * @method static Builder|ZadarmaCompanyConfig whereCreatedAt($value)
 * @method static Builder|ZadarmaCompanyConfig whereId($value)
 * @method static Builder|ZadarmaCompanyConfig whereKey($value)
 * @method static Builder|ZadarmaCompanyConfig whereSecret($value)
 * @method static Builder|ZadarmaCompanyConfig whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Zadarma\Models\ZadarmaCompanyConfigFactory factory(...$parameters)
 */
class ZadarmaCompanyConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'key',
        'secret',
    ];
}
