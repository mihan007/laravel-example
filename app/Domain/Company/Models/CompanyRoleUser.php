<?php

namespace App\Domain\Company\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\Company\Models\CompanyRoleUser
 *
 * @property int $company_id
 * @property int $user_id
 * @method static Builder|CompanyRoleUser newModelQuery()
 * @method static Builder|CompanyRoleUser newQuery()
 * @method static Builder|CompanyRoleUser query()
 * @method static Builder|CompanyRoleUser whereCompanyId($value)
 * @method static Builder|CompanyRoleUser whereUserId($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Company\Models\CompanyRoleUserFactory factory(...$parameters)
 */
class CompanyRoleUser extends Model
{
    use HasFactory;

    public $timestamps = false;
}
