<?php

namespace App\Domain\Notification\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\ApproveEmailNotification;
use App\Domain\Notification\Observers\EmailNotificationSettingObserver;
use App\Exceptions\EmailSubscriptionException;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Notification\Models\EmailNotificationSetting
 *
 * @property int $id
 * @property int $company_id
 * @property string $email
 * @property string $notification_type
 * @property string $status
 * @property string $status_changed_at
 * @property string $disable_link_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $last_sent_at
 * @property-read Company $company
 * @property-read mixed $form_data
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|EmailNotificationSetting newModelQuery()
 * @method static Builder|EmailNotificationSetting newQuery()
 * @method static Builder|EmailNotificationSetting query()
 * @method static Builder|EmailNotificationSetting whereCompanyId($value)
 * @method static Builder|EmailNotificationSetting whereCreatedAt($value)
 * @method static Builder|EmailNotificationSetting whereDeletedAt($value)
 * @method static Builder|EmailNotificationSetting whereDisableLinkKey($value)
 * @method static Builder|EmailNotificationSetting whereEmail($value)
 * @method static Builder|EmailNotificationSetting whereId($value)
 * @method static Builder|EmailNotificationSetting whereLastSentAt($value)
 * @method static Builder|EmailNotificationSetting whereNotificationType($value)
 * @method static Builder|EmailNotificationSetting whereStatus($value)
 * @method static Builder|EmailNotificationSetting whereStatusChangedAt($value)
 * @method static Builder|EmailNotificationSetting whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Notification\Models\EmailNotificationSettingFactory factory(...$parameters)
 */
class EmailNotificationSetting extends Model
{
    use Notifiable;
    use HasFactory;

    public const STATUS_DISABLED = 'disabled';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending_approve';

    public static function boot()
    {
        parent::boot();
        self::observe(new EmailNotificationSettingObserver());
    }

    public $fillable = [
        'email',
        'company_id',
        'notification_type',
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

    public function getAvailableStatutes()
    {
        return [
            self::STATUS_DISABLED,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
        ];
    }

    public function getReadableStatusWithChangeDate()
    {
        $mapping = [
            self::STATUS_DISABLED => 'Отказ от получения писем',
            self::STATUS_PENDING => 'Ждет подтверждения',
            self::STATUS_APPROVED => 'Идет',
        ];
        $start = $mapping[$this->status] ?? null;
        if (! $start) {
            return null;
        }
        $readableDate = date('d.m.Y H:i', strtotime($this->status_changed_at));

        return "{$mapping[$this->status]}($readableDate)";
    }

    /**
     * @param string $email
     * @param $emailNotificationType
     * @param $companyId
     * @throws EmailSubscriptionException
     */
    public static function setPending(string $email, $emailNotificationType, $companyId)
    {
        $setting = self::getSetting($email, $emailNotificationType, $companyId);
        $setting->setStatus(self::STATUS_PENDING);
    }

    /**
     * @param string $email
     * @param $emailNotificationType
     * @param $companyId
     * @throws EmailSubscriptionException
     */
    public static function setDisabled(string $email, $emailNotificationType, $companyId)
    {
        $setting = self::getSetting($email, $emailNotificationType, $companyId);
        $setting->setStatus(self::STATUS_DISABLED);
    }

    /**
     * @param string $email
     * @param $emailNotificationType
     * @param $companyId
     * @throws EmailSubscriptionException
     */
    public static function setApproved(string $email, $emailNotificationType, $companyId)
    {
        $setting = self::getSetting($email, $emailNotificationType, $companyId);
        $setting->setStatus(self::STATUS_APPROVED);
    }

    /**
     * @param string $status
     * @throws EmailSubscriptionException
     */
    public function setStatus(string $status)
    {
        if (! in_array($status, $this->getAvailableStatutes())) {
            throw new EmailSubscriptionException('Trying to set unknown status '.$status);
        }
        $this->status = $status;
        $this->status_changed_at = Carbon::now()->toDateTimeString();
        $this->save();
    }

    /**
     * @param string $email
     * @param $emailNotificationType
     * @param $companyId
     * @return EmailNotificationSetting
     */
    private static function getSetting(string $email, $emailNotificationType, $companyId): self
    {
        /** @var EmailNotificationSetting $setting */
        $setting = self::firstOrNew([
            'email' => $email,
            'notification_type' => $emailNotificationType,
            'company_id' => $companyId,
        ]);

        return $setting;
    }

    public static function requestToApproveEmail($email)
    {
        /** @var EmailNotificationSetting $notificationSetting */
        $notificationSetting = self::whereEmail($email)->get()->first();
        if ($notificationSetting) {
            $notificationSetting->notify(new ApproveEmailNotification($notificationSetting));
        }
    }

    public static function getPendingNotificationList(string $email)
    {
        $notificationSettings = self::where(['email' => $email, 'status' => self::STATUS_PENDING])->get();
        $result = [];
        /** @var EmailNotificationSetting $item */
        foreach ($notificationSettings as $item) {
            $readable = EmailNotification::getReadableTypeName($item->notification_type);
            if (! $readable) {
                continue;
            }
            if (! $item->company) {
                continue;
            }
            if (! isset($result[$item->company->name])) {
                $result[$item->company->name] = [];
            }
            $result[$item->company->name][] = $readable;
        }

        return $result;
    }

    public function getFormDataAttribute()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'type' => $this->notification_type,
            'readable' => $this->getReadableStatusWithChangeDate(),
            'is_admin' => EmailCompanyAdmin::where(['company_id' => $this->company_id, 'email' => $this->email])->count() > 0,
            'status' => $this->status,
        ];
    }
}
