<?php

namespace App\Domain\Roistat\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Observers\RoistatProxyLeadObserver;
use App\Support\Interfaces\GoalCounterInterface;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

use function in_array;

/**
 * App\Domain\Roistat\Models\RoistatProxyLead
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $roistat_id
 * @property string|null $title
 * @property string|null $text
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $roistat
 * @property \Illuminate\Support\Carbon|null $creation_date
 * @property string|null $order_id
 * @property string $for_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Company $company
 * @property-read mixed $is_non_targeted
 * @property-read mixed $is_not_confirmed_admin
 * @property-read mixed $is_not_confirmed
 * @property-read bool $is_not_confirmed_user
 * @property-read mixed $is_target
 * @property-read RoistatProxyLeadsReport|null $reportLead
 * @method static Builder|RoistatProxyLead newModelQuery()
 * @method static Builder|RoistatProxyLead newQuery()
 * @method static Builder|RoistatProxyLead period(Carbon $startAt, Carbon $endAt)
 * @method static Builder|RoistatProxyLead query()
 * @method static Builder|RoistatProxyLead whereCompanyId($value)
 * @method static Builder|RoistatProxyLead whereCreatedAt($value)
 * @method static Builder|RoistatProxyLead whereCreationDate($value)
 * @method static Builder|RoistatProxyLead whereEmail($value)
 * @method static Builder|RoistatProxyLead whereForDate($value)
 * @method static Builder|RoistatProxyLead whereId($value)
 * @method static Builder|RoistatProxyLead whereName($value)
 * @method static Builder|RoistatProxyLead whereOrderId($value)
 * @method static Builder|RoistatProxyLead wherePhone($value)
 * @method static Builder|RoistatProxyLead whereRoistat($value)
 * @method static Builder|RoistatProxyLead whereRoistatId($value)
 * @method static Builder|RoistatProxyLead whereText($value)
 * @method static Builder|RoistatProxyLead whereTitle($value)
 * @method static Builder|RoistatProxyLead whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatProxyLeadFactory factory(...$parameters)
 */
class RoistatProxyLead extends Model implements GoalCounterInterface
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'roistat_id',
        'title',
        'text',
        'name',
        'phone',
        'email',
        'roistat',
        'creation_date',
        'order_id',
        'for_date',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    protected $dates = [
        'creation_date',
    ];

    protected static function boot()
    {
        parent::boot();
        self::observe(RoistatProxyLeadObserver::class);
    }

    /**
     * Attach to company.
     *
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Is lead is not confirmed by user.
     *
     * @return bool
     */
    public function getIsNotConfirmedUserAttribute() :bool
    {
        return false;
    }

    /**
     * Is lead is not confirmed by admin.
     *
     * @return mixed
     */
    public function getIsNotConfirmedAdminAttribute() :bool
    {
        return false;
    }

    /**
     * Is proxy lead is not confirmed.
     *
     * @return mixed
     */
    public function getIsNotConfirmedAttribute() :bool
    {
        $this->loadMissing('reportLead');

        return (
            RoistatProxyLeadsReport::STATUS_USER_DISAGREE === $this->reportLead->user_confirmed
            && in_array($this->reportLead->admin_confirmed, [RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED, RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE], true)
            )
            || (
                RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED === $this->reportLead->user_confirmed
                && RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE === $this->reportLead->admin_confirmed
            );
    }

    /**
     * Is proxy lead is not targeted.
     *
     * @return mixed
     */
    public function getIsNonTargetedAttribute() :bool
    {
        $this->loadMissing('reportLead');

        return RoistatProxyLeadsReport::STATUS_USER_DISAGREE === $this->reportLead->user_confirmed
            && RoistatProxyLeadsReport::STATUS_ADMIN_AGREE === $this->reportLead->admin_confirmed;
    }

    /**
     * Is proxy lead is targeted.
     *
     * @return mixed
     */
    public function getIsTargetAttribute() :bool
    {
        return RoistatProxyLeadsReport::STATUS_USER_AGREE === ($this->reportLead ? $this->reportLead->user_confirmed : false);
    }

    /**
     * Attach to one lead in report.
     *
     * @return HasOne
     */
    public function reportLead()
    {
        return $this->hasOne(RoistatProxyLeadsReport::class);
    }

    /**
     * Receive positive statuses.
     *
     * @return Collection
     */
    public function getPositiveStatuses() :Collection
    {
        $lead = $this;

        return collect(['is_target', 'is_non_targeted', 'is_not_confirmed', 'is_not_confirmed_admin', 'is_not_confirmed_user'])
            ->filter(function ($status) use ($lead) {
                return true === (bool) $lead->{$status};
            })
            ->values();
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

    /**
     * Get company id and date from instance
     * It is necessary for creation goal counter for special company and date.
     *
     * @return array
     */
    public function getGoalCounterData(): array
    {
        $this->loadMissing('company');

        // company does not empty - it can only soft deleted
        if (null === $this->company) {
            $this->load(['company' => function (BelongsTo $query) {
                $query->withTrashed();
            }]);
        }

        return [
            'company_id' => $this->company->id,
            'for_date' => $this->creation_date->toDateString(),
        ];
    }
}
