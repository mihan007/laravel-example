<?php

namespace App\Domain\Roistat\Models;

use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Domain\Roistat\Models\RoistatProxyLeadsReport
 *
 * @property int $id
 * @property int $roistat_company_config_id
 * @property int $roistat_proxy_lead_id
 * @property string|null $title
 * @property string|null $text
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $roistat
 * @property string|null $order_id
 * @property string $for_date
 * @property bool $deleted
 * @property int $admin_confirmed
 * @property string|null $admin_comment Admin comment for lead report
 * @property int $user_confirmed
 * @property string|null $user_comment User comment for lead report
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read RoistatProxyLead $roistatProxyLead
 * @method static Builder|RoistatProxyLeadsReport active()
 * @method static Builder|RoistatProxyLeadsReport newModelQuery()
 * @method static Builder|RoistatProxyLeadsReport newQuery()
 * @method static Builder|RoistatProxyLeadsReport period(Carbon $startAt, Carbon $endAt)
 * @method static Builder|RoistatProxyLeadsReport query()
 * @method static Builder|RoistatProxyLeadsReport whereAdminComment($value)
 * @method static Builder|RoistatProxyLeadsReport whereAdminConfirmed($value)
 * @method static Builder|RoistatProxyLeadsReport whereCreatedAt($value)
 * @method static Builder|RoistatProxyLeadsReport whereDeleted($value)
 * @method static Builder|RoistatProxyLeadsReport whereEmail($value)
 * @method static Builder|RoistatProxyLeadsReport whereForDate($value)
 * @method static Builder|RoistatProxyLeadsReport whereId($value)
 * @method static Builder|RoistatProxyLeadsReport whereName($value)
 * @method static Builder|RoistatProxyLeadsReport whereOrderId($value)
 * @method static Builder|RoistatProxyLeadsReport wherePhone($value)
 * @method static Builder|RoistatProxyLeadsReport whereRoistat($value)
 * @method static Builder|RoistatProxyLeadsReport whereRoistatCompanyConfigId($value)
 * @method static Builder|RoistatProxyLeadsReport whereRoistatProxyLeadId($value)
 * @method static Builder|RoistatProxyLeadsReport whereText($value)
 * @method static Builder|RoistatProxyLeadsReport whereTitle($value)
 * @method static Builder|RoistatProxyLeadsReport whereUpdatedAt($value)
 * @method static Builder|RoistatProxyLeadsReport whereUserComment($value)
 * @method static Builder|RoistatProxyLeadsReport whereUserConfirmed($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatProxyLeadsReportFactory factory(...$parameters)
 */
class RoistatProxyLeadsReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'roistat_company_config_id',
        'roistat_proxy_lead_id',
        'title',
        'text',
        'name',
        'phone',
        'email',
        'roistat',
        'order_id',
        'for_date',
        'deleted',
        'admin_confirmed',
        'user_confirmed',
        'admin_comment',
        'user_comment',
    ];

    protected $casts = [
        'roistat_proxy_lead_id' => 'integer',
        'roistat_company_config_id' => 'integer',
        'admin_confirmed' => 'integer',
        'user_confirmed' => 'integer',
        'deleted' => 'boolean',
    ];

    public const STATUS_ADMIN_AGREE = 3;
    public const STATUS_ADMIN_DISAGREE = 2;
    public const STATUS_ADMIN_NOT_CONFIRMED = 1;
    public const STATUS_ADMIN_DEFAULT = 0;

    public const STATUS_USER_AGREE = 1;
    public const STATUS_USER_DISAGREE = 0;
    public const STATUS_USER_NOT_CONFIRMED = 2;

    protected static function boot()
    {
        parent::boot();

        self::updating(function (self $report) {
            $report->load('roistatProxyLead');

            if ($report->isDirty(['admin_confirmed', 'user_confirmed'])) {
                ProxyLeadGoalCounter::decrementInstance(
                    $report->roistatProxyLead,
                    $report->roistatProxyLead->getPositiveStatuses()
                );
            }
        });

        self::updated(function (self $report) {
            $report->load('roistatProxyLead');

            if ($report->isDirty(['admin_confirmed', 'user_confirmed'])) {
                ProxyLeadGoalCounter::incrementInstance(
                    $report->roistatProxyLead,
                    $report->roistatProxyLead->getPositiveStatuses()
                );
            }

            // if it is deleting
            if ($report->deleted && $report->isDirty(['deleted'])) {
                ProxyLeadGoalCounter::decrementInstance(
                    $report->roistatProxyLead,
                    $report->roistatProxyLead->getPositiveStatuses()
                );
            }

            // if it is restoring
            if (! $report->deleted && $report->isDirty(['deleted'])) {
                ProxyLeadGoalCounter::incrementInstance(
                    $report->roistatProxyLead,
                    $report->roistatProxyLead->getPositiveStatuses()
                );
            }
        });
    }

    /**
     * Belongs to roistat proxy lead.
     *
     * @return BelongsTo
     */
    public function roistatProxyLead()
    {
        return $this->belongsTo(\App\Domain\Roistat\Models\RoistatProxyLead::class);
    }

    /**
     * Get active records.
     *
     * @param $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        /* @var Builder $query */
        return $query->where('deleted', '=', '0');
    }

    /**
     * Get Data for period.
     *
     * @param $query
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @return Builder
     */
    public function scopePeriod($query, Carbon $startAt, Carbon $endAt)
    {
        /* @var Builder $query */
        return $query->where('for_date', '>=', $startAt->toDateString())
            ->where('for_date', '<=', $endAt->toDateString());
    }
}
