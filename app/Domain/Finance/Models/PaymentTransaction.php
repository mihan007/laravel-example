<?php

namespace App\Domain\Finance\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Observers\PaymentTransactionObserver;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\Tinkoff\Services\TinkoffService;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\Finance\Models\PaymentTransaction
 *
 * @property int $id
 * @property int $company_id
 * @property string $payment_type
 * @property float $amount
 * @property float $balance
 * @property string|null $company_name
 * @property int|null $company_inn
 * @property string $status
 * @property string|null $operation
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @property string|null $information
 * @property int|null $account_number
 * @property int $proxy_leads_id
 * @property-read Company $company
 * @property-read boolean $paidByTinkoff
 * @property-read \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
 * @method static Builder|PaymentTransaction newModelQuery()
 * @method static Builder|PaymentTransaction newQuery()
 * @method static Builder|PaymentTransaction query()
 * @method static Builder|PaymentTransaction whereAccountNumber($value)
 * @method static Builder|PaymentTransaction whereAmount($value)
 * @method static Builder|PaymentTransaction whereCompanyId($value)
 * @method static Builder|PaymentTransaction whereCompanyInn($value)
 * @method static Builder|PaymentTransaction whereCompanyName($value)
 * @method static Builder|PaymentTransaction whereCreatedAt($value)
 * @method static Builder|PaymentTransaction whereId($value)
 * @method static Builder|PaymentTransaction whereInformation($value)
 * @method static Builder|PaymentTransaction whereOperation($value)
 * @method static Builder|PaymentTransaction wherePaymentType($value)
 * @method static Builder|PaymentTransaction whereProxyLeadsId($value)
 * @method static Builder|PaymentTransaction whereSourceOfChanges($value)
 * @method static Builder|PaymentTransaction whereStatus($value)
 * @method static Builder|PaymentTransaction whereUpdatedAt($value)
 * @method static Builder|PaymentTransaction income()
 * @method static Builder|PaymentTransaction expense()
 * @method static Builder|PaymentTransaction timePeriod($startAt, $endAt)
 * @mixin Eloquent
 * @property-read bool $paid_by_tinkoff
 * @method static Builder|PaymentTransaction expenseForProxyLead()
 * @method static \Database\Factories\Domain\Finance\Models\PaymentTransactionFactory factory(...$parameters)
 * @method static Builder|PaymentTransaction incomeWithoutMoneyBack()
 * @method static Builder|PaymentTransaction moneyBack()
 * @method static Builder|PaymentTransaction notPaidInvoice()
 * @method static Builder|PaymentTransaction notPaidNotInvoice()
 * @method static Builder|PaymentTransaction whereBalance($value)
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';
    public const STATUS_NOT_PAID = 'not_paid';
    public const STATUS_WRITE_OFF = 'write-off';

    public const OPERATION_REPLENISHMENT = 'replenishment';
    public const OPERATION_RETURN = 'return';
    public const OPERATION_WRITE_OFF = 'write-off';
    public const OPERATION_NOT_PAID = 'not_paid';

    public const FILTER_INCOME = "income";
    public const FILTER_EXPENSE = "expense";
    public const FILTER_MONEY_BACK = "moneyback";
    public const FILTER_NOT_PAID = "not_paid";
    public const FILTER_INVOICED = "invoiced";

    public $fillable = ['information', 'amount'];

    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
        'updated_at' => 'datetime:d.m.Y H:i:s',
        'balance' => 'integer',
    ];

    public static $operations = [
        self::OPERATION_REPLENISHMENT => 'Пополнение',
        self::OPERATION_WRITE_OFF => 'Списание',
        self::OPERATION_NOT_PAID => 'Не оплачен',
        self::OPERATION_RETURN => 'Возврат средств',
    ];

    public const PAYMENT_TYPE_TINKOFF = 'invoice_tinkoff';
    public const PAYMENT_TYPE_YANDEX_MONEY = 'yandex_money_pc';
    public const PAYMENT_TYPE_CREDIT_CARD = 'yandex_money_ac';
    public const PAYMENT_TYPE_INSIDE = 'inside';
    public const PAYMENT_TYPE_MANUAL = 'balance_operations';

    public static $replinishmentSource = [
        self::PAYMENT_TYPE_TINKOFF,
        self::PAYMENT_TYPE_YANDEX_MONEY,
        self::PAYMENT_TYPE_CREDIT_CARD,
    ];

    public static $replinishmentSourceNames = [
        self::PAYMENT_TYPE_TINKOFF => 'Р/C',
        self::PAYMENT_TYPE_YANDEX_MONEY => 'Яндекс.Деньги',
        self::PAYMENT_TYPE_CREDIT_CARD => 'Банковская карта',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(PaymentTransactionObserver::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isReduceBalance()
    {
        return in_array($this->operation, [self::OPERATION_WRITE_OFF]);
    }

    public function proxyLead()
    {
        return $this->belongsTo(ProxyLead::class, 'proxy_leads_id');
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncome($query)
    {
        return $query->where('operation', '<>', self::OPERATION_WRITE_OFF)
            ->where('status', '<>', PaymentTransaction::STATUS_NOT_PAID);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncomeWithoutMoneyBack($query)
    {
        return $query->where('operation', '=', self::OPERATION_REPLENISHMENT)
            ->where('payment_type', '<>', self::PAYMENT_TYPE_INSIDE)
            ->where('status', '<>', PaymentTransaction::STATUS_NOT_PAID);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMoneyBack($query)
    {
        return $query->where('operation', '=', self::OPERATION_REPLENISHMENT)
            ->where('payment_type', '=', self::PAYMENT_TYPE_INSIDE)
            ->where('status', '<>', PaymentTransaction::STATUS_NOT_PAID);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotPaidInvoice($query)
    {
        return $query->where('status', '=', PaymentTransaction::STATUS_NOT_PAID)
            ->where('payment_type', '=', PaymentTransaction::PAYMENT_TYPE_TINKOFF);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotPaidNotInvoice($query)
    {
        return $query->where('status', '=', PaymentTransaction::STATUS_NOT_PAID)
            ->where('payment_type', '<>', PaymentTransaction::PAYMENT_TYPE_TINKOFF);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpense($query)
    {
        return $query->where('operation', '=', self::OPERATION_WRITE_OFF);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpenseForProxyLead($query)
    {
        return $query->where('operation', '=', self::OPERATION_WRITE_OFF)
            ->where('payment_type', self::PAYMENT_TYPE_INSIDE);
    }

    /**
     * Scope a query to only income
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTimePeriod($query, $startAt, $endAt)
    {
        return $query->whereBetween('updated_at', [$startAt, $endAt]);
    }

    public function getPaidByTinkoffAttribute(): bool
    {
        return strpos($this->information, TinkoffService::INFORMATION_PREFIX) !== false;
    }
}
