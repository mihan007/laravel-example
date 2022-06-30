<?php

namespace App\Domain\Notification\Models;

use App\Domain\Notification\Observers\EmailManageLinkObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Notification\Models\EmailManageLink
 *
 * @property int $id
 * @property string $email
 * @property string $approve_all_pending_key
 * @property string $notification_settings_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string $disable_all_key
 * @method static Builder|EmailManageLink newModelQuery()
 * @method static Builder|EmailManageLink newQuery()
 * @method static Builder|EmailManageLink query()
 * @method static Builder|EmailManageLink whereApproveAllPendingKey($value)
 * @method static Builder|EmailManageLink whereCreatedAt($value)
 * @method static Builder|EmailManageLink whereDeletedAt($value)
 * @method static Builder|EmailManageLink whereDisableAllKey($value)
 * @method static Builder|EmailManageLink whereEmail($value)
 * @method static Builder|EmailManageLink whereId($value)
 * @method static Builder|EmailManageLink whereNotificationSettingsKey($value)
 * @method static Builder|EmailManageLink whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmailManageLink extends Model
{
    public $fillable = ['email'];

    public static function boot()
    {
        parent::boot();
        self::observe(new EmailManageLinkObserver());
    }

    public static function init($email)
    {
        self::firstOrCreate(['email' => $email]);
    }

    public static function getUnsubscribeAllUrl($email)
    {
        /** @var EmailManageLink $emailManageLink */
        $emailManageLink = self::whereEmail($email)->get()->first();

        if (! $emailManageLink) {
            return false;
        }

        return route('subscription.unsubscribe.all', ['key' => $emailManageLink->disable_all_key]);
    }

    public static function getSettingsUrl($email)
    {
        /** @var EmailManageLink $emailManageLink */
        $emailManageLink = self::whereEmail($email)->get()->first();

        if (! $emailManageLink) {
            return false;
        }

        return route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
    }

    public static function getSubscribePendingUrl($email)
    {
        /** @var EmailManageLink $emailManageLink */
        $emailManageLink = self::whereEmail($email)->get()->first();

        if (! $emailManageLink) {
            return false;
        }

        return route('subscription.subscribe.pending', ['key' => $emailManageLink->approve_all_pending_key]);
    }

    public static function getDisabledLinkUrl($email, $notification_type, $companyId)
    {
        /** @var EmailManageLink $emailManageLink */
        $emailManageLink = EmailNotificationSetting::where('email', $email)
            ->where('notification_type', $notification_type)
            ->where('company_id', $companyId)
            ->first();

        if (! $emailManageLink) {
            return false;
        }

        return url('unsubscribe/'.$emailManageLink->disable_link_key);
    }
}
