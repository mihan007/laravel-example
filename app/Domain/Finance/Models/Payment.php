<?php

namespace App\Domain\Finance\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Finance\Models\Payment
 *
 * @property int $id
 * @property int $finance_report_id
 * @property float $amount Amount of payment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FinanceReport $financeReport
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment query()
 * @method static Builder|Payment whereAmount($value)
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereFinanceReportId($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Finance\Models\PaymentFactory factory(...$parameters)
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_report_id',
        'amount',
    ];

    protected $casts = [
        'finance_report_id' => 'integer',
        'amount' => 'double',
    ];

    /**
     * It belongs to finance report.
     *
     * @return BelongsTo
     */
    public function financeReport(): BelongsTo
    {
        return $this->belongsTo(FinanceReport::class);
    }
}
