<?php

namespace App\Domain\Notification\Models;

use App\Domain\Company\Models\Company;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Notification\Models\EmailCompanyAdmin
 *
 * @property int $id
 * @property int $company_id
 * @property string $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Company $company
 * @method static Builder|EmailCompanyAdmin newModelQuery()
 * @method static Builder|EmailCompanyAdmin newQuery()
 * @method static Builder|EmailCompanyAdmin query()
 * @method static Builder|EmailCompanyAdmin whereCompanyId($value)
 * @method static Builder|EmailCompanyAdmin whereCreatedAt($value)
 * @method static Builder|EmailCompanyAdmin whereDeletedAt($value)
 * @method static Builder|EmailCompanyAdmin whereEmail($value)
 * @method static Builder|EmailCompanyAdmin whereId($value)
 * @method static Builder|EmailCompanyAdmin whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmailCompanyAdmin extends Model
{
    protected $fillable = [
        'company_id',
        'email',
    ];

    /**
     * It belongs to company.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
