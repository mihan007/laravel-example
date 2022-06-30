<?php

namespace App\Domain\Company\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Company\Models\CompanyReplacementDatabaseConfig
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $login
 * @property string $password
 * @property string $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CompanyReplacementDatabaseConfig newModelQuery()
 * @method static Builder|CompanyReplacementDatabaseConfig newQuery()
 * @method static Builder|CompanyReplacementDatabaseConfig query()
 * @method static Builder|CompanyReplacementDatabaseConfig whereComment($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereCompanyId($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereCreatedAt($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereId($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereLogin($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereName($value)
 * @method static Builder|CompanyReplacementDatabaseConfig wherePassword($value)
 * @method static Builder|CompanyReplacementDatabaseConfig whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CompanyReplacementDatabaseConfig extends Model
{
    protected $fillable = [
        'name',
        'login',
        'password',
        'comment',
    ];
}
