<?php

namespace App\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Observers\PlReportLeadObserver;
use App\Support\Constants\CompanyLeadStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\PlReportLead
 *
 * @property int $id
 * @property int $proxy_lead_id
 * @property int $company_confirmed
 * @property int|null $reasons_of_rejection_id Rejection reason
 * @property string|null $company_comment
 * @property int $admin_confirmed
 * @property string|null $admin_comment
 * @property int $total_confirmed
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $not_before_called_counter
 * @property string|null $photo_url
 * @property int|null $moderation_status
 * @property int $is_send
 * @property-read bool $is_non_targeted
 * @property-read bool $is_not_confirmed_admin
 * @property-read bool $is_not_confirmed
 * @property-read bool $is_not_confirmed_user
 * @property-read bool $is_target
 * @property-read ProxyLead $proxyLead
 * @property-read ReasonsOfRejection|null $reason
 * @method static Builder|PlReportLead newModelQuery()
 * @method static Builder|PlReportLead newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlReportLead onlyTrashed()
 * @method static Builder|PlReportLead query()
 * @method static Builder|PlReportLead whereAdminComment($value)
 * @method static Builder|PlReportLead whereAdminConfirmed($value)
 * @method static Builder|PlReportLead whereCompanyComment($value)
 * @method static Builder|PlReportLead whereCompanyConfirmed($value)
 * @method static Builder|PlReportLead whereCreatedAt($value)
 * @method static Builder|PlReportLead whereDeletedAt($value)
 * @method static Builder|PlReportLead whereId($value)
 * @method static Builder|PlReportLead whereIsSend($value)
 * @method static Builder|PlReportLead whereModerationStatus($value)
 * @method static Builder|PlReportLead whereNotBeforeCalledCounter($value)
 * @method static Builder|PlReportLead wherePhotoUrl($value)
 * @method static Builder|PlReportLead whereProxyLeadId($value)
 * @method static Builder|PlReportLead whereReasonsOfRejectionId($value)
 * @method static Builder|PlReportLead whereTotalConfirmed($value)
 * @method static Builder|PlReportLead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlReportLead withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlReportLead withoutTrashed()
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\PlReportLeadFactory factory(...$parameters)
 */
class PlReportLead extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const STATUS_AGREE = 1;
    public const STATUS_DISAGREE = 0;
    public const STATUS_NOT_CONFIRMED = 2;
    public const STATUS_DOUBLE_APPLICATION = 6; //дубль заявки

    public const COMPANY_STATUS_COULD_NOT_REACH_BY_PHONE = 5; //не дозвонились

    protected $fillable = [
        'company_confirmed',
        'company_comment',
        'admin_confirmed',
        'admin_comment',
        'total_confirmed',
        'reasons_of_rejection_id',
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(PlReportLeadObserver::class);
    }

    /**
     * Toggle admin statuses.
     *
     * @param $status
     * @return $this
     */
    public function adminConfirmation($status): self
    {
        $validStatus = (int) $status;

        if (self::STATUS_AGREE === $validStatus && self::STATUS_DISAGREE === $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_AGREE;
        }

        if (self::STATUS_DISAGREE === $validStatus && self::STATUS_AGREE !== $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_DISAGREE;
            $this->company_confirmed = self::STATUS_AGREE;
        }

        if (self::STATUS_AGREE === $validStatus && self::STATUS_NOT_CONFIRMED === $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_AGREE;
        }

        if (self::STATUS_AGREE === $validStatus && self::STATUS_AGREE !== $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_AGREE;
        }

        if (self::STATUS_DISAGREE === $validStatus && self::STATUS_AGREE == $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_DISAGREE;
            $this->company_confirmed = self::STATUS_AGREE;
        }

        if (self::STATUS_AGREE === $validStatus && self::STATUS_AGREE == $this->company_confirmed) {
            $this->admin_confirmed = self::STATUS_AGREE;
        }

        if ($this->isDirty()) {
            $this->save();
        }

        return $this;
    }

    public function getIsNotConfirmedUserAttribute(): bool
    {
        return self::STATUS_NOT_CONFIRMED === $this->company_confirmed;
    }

    public function getIsNotConfirmedAdminAttribute(): bool
    {
        return self::STATUS_NOT_CONFIRMED === $this->admin_confirmed;
    }

    public function getIsNotConfirmedAttribute(): bool
    {
        return ! $this->is_target && ! $this->is_non_targeted;
    }

    public function getIsNonTargetedAttribute(): bool
    {
        return self::STATUS_AGREE !== $this->company_confirmed;
    }

    public function getIsTargetAttribute(): bool
    {
        return self::STATUS_AGREE === $this->company_confirmed;
    }

    /**
     * Attach to proxy lead.
     *
     * @return BelongsTo
     */
    public function proxyLead(): BelongsTo
    {
        return $this->belongsTo(ProxyLead::class);
    }

    /**
     * Attach to reason.
     *
     * @return BelongsTo
     */
    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReasonsOfRejection::class, 'reasons_of_rejection_id', 'id');
    }

    /**
     * Toggle user statuses.
     *
     * @param $status
     * @return $this
     */
    public function userConfirmation($status): self
    {
        $validStatus = (int) $status;
        $this->company_confirmed = $validStatus;
        if ($this->company_confirmed === CompanyLeadStatus::DUPLICATE) {
            $this->reasons_of_rejection_id = 1;
        }
        $this->admin_confirmed = self::STATUS_AGREE === $validStatus ? self::STATUS_AGREE : self::STATUS_NOT_CONFIRMED;
        $this->save();

        return $this;
    }
}
