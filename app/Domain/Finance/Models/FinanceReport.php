<?php

namespace App\Domain\Finance\Models;

use App\Domain\Company\Models\Company;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Finance\Models\FinanceReport
 *
 * @property int $id
 * @property int $company_id
 * @property int $status Report status
 * @property int $lead_count Amount of target leads
 * @property float $paid Amount that company paid us
 * @property float $lead_cost One lead costs
 * @property float $to_pay Amount that company have to pay to us
 * @property string $for_date Report period
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Collection|Payment[] $payments
 * @property-read int|null $payments_count
 * @method static Builder|FinanceReport newModelQuery()
 * @method static Builder|FinanceReport newQuery()
 * @method static Builder|FinanceReport query()
 * @method static Builder|FinanceReport whereCompanyId($value)
 * @method static Builder|FinanceReport whereCreatedAt($value)
 * @method static Builder|FinanceReport whereForDate($value)
 * @method static Builder|FinanceReport whereId($value)
 * @method static Builder|FinanceReport whereLeadCost($value)
 * @method static Builder|FinanceReport whereLeadCount($value)
 * @method static Builder|FinanceReport wherePaid($value)
 * @method static Builder|FinanceReport whereStatus($value)
 * @method static Builder|FinanceReport whereToPay($value)
 * @method static Builder|FinanceReport whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Finance\Models\FinanceReportFactory factory(...$parameters)
 */
class FinanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'lead_count',
        'paid',
        'lead_cost',
        'to_pay',
        'for_date',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'status' => 'integer',
        'lead_count' => 'integer',
        'paid' => 'double',
        'lead_cost' => 'double',
        'to_pay' => 'double',
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

    /**
     * It can have payments.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
