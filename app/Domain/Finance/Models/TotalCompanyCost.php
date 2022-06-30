<?php

namespace App\Domain\Finance\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Finance\Models\TotalCompanyCost
 *
 * @property int $id
 * @property int $company_id
 * @property string $amount Amounts of costs per month
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|TotalCompanyCost newModelQuery()
 * @method static Builder|TotalCompanyCost newQuery()
 * @method static Builder|TotalCompanyCost query()
 * @method static Builder|TotalCompanyCost whereAmount($value)
 * @method static Builder|TotalCompanyCost whereCompanyId($value)
 * @method static Builder|TotalCompanyCost whereCreatedAt($value)
 * @method static Builder|TotalCompanyCost whereId($value)
 * @method static Builder|TotalCompanyCost whereUpdatedAt($value)
 * @mixin Eloquent
 */
/** @deprecated */
class TotalCompanyCost extends Model
{
    use HasFactory;

    protected $fillable = ['amount'];
}
