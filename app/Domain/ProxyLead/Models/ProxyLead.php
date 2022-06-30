<?php

namespace App\Domain\ProxyLead\Models;

use Algo26\IdnaConvert\ToUnicode;
use App\Domain\ProxyLead\Observers\ProxyLeadObserver;
use App\Support\Casts\UserTimezoneCast;
use App\Support\Constants\StatusTitle;
use App\Support\Interfaces\GoalCounterInterface;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Domain\ProxyLead\Models\ProxyLead
 *
 * @property int $id
 * @property int $proxy_lead_setting_id
 * @property int|null $cost
 * @property string|null $phone
 * @property string $title
 * @property string|null $name
 * @property string|null $comment
 * @property int|null $ym_counter
 * @property string|null $advertising_platform
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $tag This tag helps understand who is this lead attach in advertising system
 * @property string|null $service_id id service
 * @property mixed|null $extra Extra data as json
 * @property int $is_free
 * @property-read mixed $company
 * @property-read mixed $is_non_targeted
 * @property-read mixed $is_not_confirmed_admin
 * @property-read mixed $is_not_confirmed
 * @property-read mixed $is_not_confirmed_user
 * @property-read mixed $is_target
 * @property-read mixed $readable_advertising_platform
 * @property-read mixed $readable_phone
 * @property-read mixed $status_title
 * @property-read string $formatted_info
 * @property-read string $formatted_approve_status
 * @property-read ProxyLeadSetting $proxyLeadSetting
 * @property-read PlReportLead|null $reportLead
 * @property-read PlReportLead|null $reportLeadWithTrashed
 * @method static Builder|ProxyLead newModelQuery()
 * @method static Builder|ProxyLead newQuery()
 * @method static Builder|ProxyLead notDeleted()
 * @method static \Illuminate\Database\Query\Builder|ProxyLead onlyTrashed()
 * @method static Builder|ProxyLead period(Carbon $startAt, Carbon $endAt)
 * @method static Builder|ProxyLead query()
 * @method static Builder|ProxyLead whereAdvertisingPlatform($value)
 * @method static Builder|ProxyLead whereComment($value)
 * @method static Builder|ProxyLead whereCost($value)
 * @method static Builder|ProxyLead whereCreatedAt($value)
 * @method static Builder|ProxyLead whereDeletedAt($value)
 * @method static Builder|ProxyLead whereExtra($value)
 * @method static Builder|ProxyLead whereId($value)
 * @method static Builder|ProxyLead whereIsFree($value)
 * @method static Builder|ProxyLead whereName($value)
 * @method static Builder|ProxyLead wherePhone($value)
 * @method static Builder|ProxyLead whereProxyLeadSettingId($value)
 * @method static Builder|ProxyLead whereServiceId($value)
 * @method static Builder|ProxyLead whereTag($value)
 * @method static Builder|ProxyLead whereTitle($value)
 * @method static Builder|ProxyLead whereUpdatedAt($value)
 * @method static Builder|ProxyLead whereYmCounter($value)
 * @method static \Illuminate\Database\Query\Builder|ProxyLead withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ProxyLead withoutTrashed()
 * @mixin Eloquent
 * @property int|null $status Statu's lead
 * @property-read bool $is_expired
 * @method static \Database\Factories\Domain\ProxyLead\Models\ProxyLeadFactory factory(...$parameters)
 * @method static Builder|ProxyLead whereStatus($value)
 */
class ProxyLead extends Model implements GoalCounterInterface
{
    use SoftDeletes;
    use HasFactory;

    public const TEST_ROISTAT_NAME = 'Тест Ройстат';
    public const IMPORTANT_PHONE_DIGITS_COUNT_FROM_THE_END = 10;

    protected $fillable = [
        'proxy_lead_setting_id',
        'phone',
        'title',
        'name',
        'comment',
        'ym_counter',
        'tag',
        'service_id',
        'advertising_platform',
        'extra',
        'not_before_called_counter',
        'is_free',
        'status',
        'deleted_at',
    ];

    protected $appends = [
        'readable_advertising_platform',
        'is_expired'
    ];

    protected $casts = [
        'proxy_lead_setting_id' => 'integer',
        'ym_counter' => 'integer'
    ];

    // protected $with = ['reportLead' , 'reportLead.reason'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(ProxyLeadObserver::class);
    }

    public function getIsNotConfirmedUserAttribute()
    {
        $this->loadMissing('reportLead');

        return $this->reportLead->is_not_confirmed_user;
    }

    /**
     * Is lead is not confirmed by admin.
     *
     * @return mixed
     */
    public function getIsNotConfirmedAdminAttribute()
    {
        $this->loadMissing('reportLead');

        return $this->reportLead->is_not_confirmed_admin;
    }

    /**
     * Is proxy lead is not confirmed.
     *
     * @return mixed
     */
    public function getIsNotConfirmedAttribute()
    {
        $this->loadMissing('reportLead');

        return $this->reportLead->is_not_confirmed;
    }

    /**
     * Is proxy lead is not targeted.
     *
     * @return mixed
     */
    public function getIsNonTargetedAttribute()
    {
        $this->loadMissing('reportLead');

        return $this->reportLead->is_non_targeted;
    }

    /**
     * Is proxy lead is targeted.
     *
     * @return mixed
     */
    public function getIsTargetAttribute()
    {
        $this->loadMissing('reportLead');

        return $this->reportLead->is_target;
    }

    public function getExtraAttribute($value)
    {
        $result = json_decode($value, true);
        if (! is_array($result)) {
            return [];
        } else {
            return $result;
        }
    }

    public function setExtraAttribute($value)
    {
        $this->attributes['extra'] = json_encode($value);
    }

    public function getAdvertisingPlatformAttribute()
    {
        $url = trim($this->getAttributeFromArray('advertising_platform'));

        if (preg_match('/^(http|https|ftp|sftp)\:\/\//', $url)) {
            return $url;
        } elseif (preg_match('/^mailto\:/', $url)) {
            return $url;
        } elseif (empty($url)) {
            return $url;
        }

        return "http://{$url}";
    }

    public function getReadableAdvertisingPlatformAttribute()
    {
        $url = $this->advertising_platform;
        $idn = new ToUnicode();

        return urldecode($idn->convertUrl($url));
    }

    public function getStatusTitleAttribute()
    {
        return StatusTitle::STATUSES[$this->extra['status']] ?? $this->extra['status'];
    }

    /**
     * Receive positive statuses.
     *
     * @return Collection
     */
    public function getPositiveStatuses(): Collection
    {
        $lead = $this;

        return collect(
            ['is_target', 'is_non_targeted', 'is_not_confirmed', 'is_not_confirmed_admin', 'is_not_confirmed_user']
        )
            ->filter(
                function ($status) use ($lead) {
                    return true === (bool) $lead->{$status};
                }
            )
            ->values();
    }

    public function proxyLeadSetting()
    {
        return $this->belongsTo(ProxyLeadSetting::class);
    }

    /**
     * Has one report lead.
     *
     * @return HasOne
     */
    public function reportLead()
    {
        return $this->reportLeadWithTrashed();
    }

    /**
     * has one report lead (trashed or not trashed).
     *
     * @return mixed
     */
    public function reportLeadWithTrashed()
    {
        return $this->hasOne(PlReportLead::class)->withTrashed();
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * @param Builder $query
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @return Builder
     */
    public function scopePeriod($query, Carbon $startAt, Carbon $endAt)
    {
        return $query->whereBetween('created_at', [$startAt, $endAt]);
    }

    /**
     * Get company id and date from instance
     * It is necessary for creation goal counter for special company and date.
     *
     * @return array
     */
    public function getGoalCounterData(): array
    {
        $this->loadMissing('proxyLeadSetting.company');

        // company does not empty - it can only soft deleted
        if (null === $this->company) {
            $this->load(
                [
                    'proxyLeadSetting.company' => function (BelongsTo $query) {
                        $query->withTrashed();
                    },
                ]
            );
        }

        return [
            'company_id' => $this->proxyLeadSetting->company->id,
            'for_date' => $this->created_at->toDateString(),
            'lead_cost' => $this->cost,
        ];
    }

    public function getCompanyAttribute()
    {
        return optional($this->proxyLeadSetting)->company;
    }

    public function isTestRoistat()
    {
        return $this->name === self::TEST_ROISTAT_NAME;
    }

    public static function phoneMeaningPart($phone)
    {
        return substr(trim($phone), -self::IMPORTANT_PHONE_DIGITS_COUNT_FROM_THE_END);
    }

    public function getReadablePhoneAttribute()
    {
        if ($this->phone[0] === '7') {
            return '+'.$this->phone;
        }

        return $this->phone;
    }

    public function getPhoneAttribute($value)
    {
        return trim($value);
    }

    public function getNameAttribute($value)
    {
        return trim($value);
    }

    public function getCommentAttribute($value)
    {
        return trim($value);
    }

    public function getFormattedInfoAttribute()
    {
        $result = [];
        if (! empty($this->comment)) {
            $result[] = $this->comment;
        }

        if (! empty($this->extra)) {
            foreach ($this->extra as $name => $value) {
                if ($name === 'visit_id') {
                    $roistatValue = $value ?? 'нет';
                    $result[] = "Roistat: {$roistatValue}";
                } elseif ($name === 'callee') {
                    $result[] = "Подменный номер: {$value}";
                } elseif ($name === 'duration') {
                    $result[] = "Время разговора: {$value} сек";
                } elseif ($name === 'advertising_platform') {
                    $result[] = "Сайт: <a href='{$value}' target='_blank'>ссылка на источника трафика</a>";
                } elseif ($name === 'status') {
                    $statusValue = StatusTitle::STATUSES[$value] ?? $value;
                    $result[] = "Статус: {$statusValue}";
                }
            }
        }

        if (! empty($this->advertising_platform)) {
            $result[] = "Сайт: <a href='{$this->readable_advertising_platform}'>Ссылка на источник трафика</a>";
        }

        return implode("\n ", $result);
    }

    public function getFormattedApproveStatusAttribute()
    {
        switch ($this->reportLead->admin_confirmed) {
            case 1:
                return 'done';
                break;

            case 0:
                return 'none';
                break;

            case 2:
                return 'warning';
                break;

            default:
                return '';
        }
    }

    public function getIsExpiredAttribute(): bool
    {
        return now()->diffInDays($this->created_at) > $this->company->application_moderation_period;
    }
}
