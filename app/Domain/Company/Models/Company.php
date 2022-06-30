<?php

namespace App\Domain\Company\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Company\Financing;
use App\Domain\Company\Observers\CompanyObserver;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\Finance\Models\TotalCompanyCost;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\User\Models\User;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use App\Domain\YandexDirect\Models\YandexDirectCompanyConfig;
use App\Domain\Zadarma\Models\ZadarmaCompanyConfig;
use App\Exceptions;
use App\Models\Site;
use Carbon\Carbon;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

/**
 * App\Domain\Company\Models\Company
 *
 * @property int $id
 * @property int|null $channel_id
 * @property string $public_id Uuid for public access to company
 * @property string $name
 * @property string|null $description
 * @property bool $check_for_graph Check company for display some information in graphs
 * @property float $lead_cost Set current price for lead price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $balance
 * @property int $prepayment
 * @property int $amount_limit
 * @property string|null $date_stop_leads
 * @property int $free_period
 * @property int $balance_limit
 * @property int|null $application_moderation_period
 * @property string $manage_subscription_key
 * @property string|null $approve_description Approve description
 * @property int|null $balance_stop
 * @property int|null $balance_send_notification
 * @property int|null $account_id
 * @property-read Account|null $account
 * @property-read Channel|null $channel
 * @property-read \App\Domain\Finance\Models\TotalCompanyCost|null $costsInCurrentMonth
 * @property-read Collection|EmailNotificationSetting[] $customerBalanceLimitNotifications
 * @property-read int|null $customer_balance_limit_notifications_count
 * @property-read Collection|EmailNotificationSetting[] $emailNotifications
 * @property-read int|null $email_notifications_count
 * @property-read Collection|FinanceReport[] $financeReports
 * @property-read int|null $finance_reports_count
 * @property-read mixed $approved_notifications
 * @property-read mixed $disabled_notifications
 * @property-read mixed $email
 * @property-read string $google_status
 * @property-read mixed $pending_notifications
 * @property-read mixed $roistat_status
 * @property-read mixed $yandex_status
 * @property-read Collection|User[] $mainNotifications
 * @property-read int|null $main_notifications_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|\App\Domain\Finance\Models\PaymentTransaction[] $paymentTransaction
 * @property-read int|null $payment_transaction_count
 * @property-read Collection|\App\Domain\ProxyLead\Models\ProxyLeadGoalCounter[] $proxyLeadGoalCounters
 * @property-read int|null $proxy_lead_goal_counters_count
 * @property-read ProxyLeadSetting|null $proxyLeadSettings
 * @property-read Collection|\App\Domain\Notification\Models\EmailNotificationSetting[] $recipientsNotifications
 * @property-read int|null $recipients_notifications_count
 * @property-read Collection|CompanyReplacementDatabaseConfig[] $replacementDatabaseConfigs
 * @property-read int|null $replacement_database_configs_count
 * @property-read Collection|\App\Domain\Notification\Models\EmailNotificationSetting[] $reportNotifications
 * @property-read int|null $report_notifications_count
 * @property-read RcBalanceConfig|null $roistatBalanceConfig
 * @property-read Collection|EmailNotificationSetting[] $roistatBalanceNotifications
 * @property-read int|null $roistat_balance_notifications_count
 * @property-read \App\Domain\Roistat\Models\RoistatCompanyConfig|null $roistatConfig
 * @property-read Collection|EmailNotificationSetting[] $roistatGoogleEmailNotifications
 * @property-read int|null $roistat_google_email_notifications_count
 * @property-read Collection|RoistatProxyLead[] $roistatMostRecentProxyLeads
 * @property-read int|null $roistat_most_recent_proxy_leads_count
 * @property-read Collection|RoistatProxyLead[] $roistatProxyLeads
 * @property-read Collection|ProxyLead[] $proxyLeads
 * @property-read int|null $roistat_proxy_leads_count
 * @property-read Collection|\App\Domain\Roistat\Models\RoistatStatistic[] $roistatStatistics
 * @property-read int|null $roistat_statistics_count
 * @property-read Collection|Site[] $sites
 * @property-read int|null $sites_count
 * @property-read Collection|TotalCompanyCost[] $totalCosts
 * @property-read int|null $total_costs_count
 * @property-read Collection|\App\Domain\YandexDirect\Models\YandexDirectBalance[] $yandexBalances
 * @property-read int|null $yandex_balances_count
 * @property-read \App\Domain\YandexDirect\Models\YandexDirectCompanyConfig|null $yandexDirectConfig
 * @property-read Collection|\App\Domain\Notification\Models\EmailNotificationSetting[] $yandexDirectEmailNotifications
 * @property-read int|null $yandex_direct_email_notifications_count
 * @property-read \App\Domain\Zadarma\Models\ZadarmaCompanyConfig|null $zadarmaConfig
 * @property-read string $timezone
 * @method static Builder|Company newModelQuery()
 * @method static Builder|Company newQuery()
 * @method static \Illuminate\Database\Query\Builder|Company onlyTrashed()
 * @method static Builder|Company query()
 * @method static Builder|Company whereAccountId($value)
 * @method static Builder|Company whereAmountLimit($value)
 * @method static Builder|Company whereApplicationModerationPeriod($value)
 * @method static Builder|Company whereApproveDescription($value)
 * @method static Builder|Company whereBalance($value)
 * @method static Builder|Company whereBalanceLimit($value)
 * @method static Builder|Company whereBalanceSendNotification($value)
 * @method static Builder|Company whereBalanceStop($value)
 * @method static Builder|Company whereChannelId($value)
 * @method static Builder|Company whereCheckForGraph($value)
 * @method static Builder|Company whereCreatedAt($value)
 * @method static Builder|Company whereDateStopLeads($value)
 * @method static Builder|Company whereDeletedAt($value)
 * @method static Builder|Company whereDescription($value)
 * @method static Builder|Company whereFreePeriod($value)
 * @method static Builder|Company whereId($value)
 * @method static Builder|Company whereLeadCost($value)
 * @method static Builder|Company whereManageSubscriptionKey($value)
 * @method static Builder|Company whereName($value)
 * @method static Builder|Company wherePrepayment($value)
 * @method static Builder|Company wherePublicId($value)
 * @method static Builder|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Company withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Company withoutTrashed()
 * @mixin Eloquent
 * @property int $profit_calculate
 * @property-read int|null $proxy_leads_count
 * @method static \Database\Factories\Domain\Company\Models\CompanyFactory factory(...$parameters)
 * @method static Builder|Company whereProfitCalculate($value)
 */
class Company extends Model
{
    use Financing;
    use Notifiable;
    use SoftDeletes;
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::observe(CompanyObserver::class);
    }

    protected $fillable = [
        'name',
        'description',
        'check_for_graph',
        'public_id',
        'channel_id',
        'lead_cost',
        'prepayment',
        'free_period',
        'balance_limit',
        'application_moderation_period',
        'manage_subscription_key',
        'approve_description',
        'balance_stop',
        'balance_send_notification',
        'account_id',
        'profit_calculate',
        'amount_limit',
    ];

    protected $casts = [
        'lead_cost' => 'double',
        'check_for_graph' => 'bool',
        'balance' => 'integer'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public const GOOGLE_HAS_ERRORS = 'has_errors';
    public const GOOGLE_NOT_CONFIGURED = 'not_configured';
    public const GOOGLE_OK = 'ok';

    public const ROISTAT_HAS_ERRORS = 'has_errors';
    public const ROISTAT_IS_NOT_OONFIGURED = 'not_configured';
    public const ROISTAT_OK = 'ok';

    public const YANDEX_HAS_ERRORS = 'has_errors';
    public const YANDEX_IS_NOT_CONFIGURED = 'not_configured';
    public const YANDEX_OK = 'ok';

    public function getEmailAttribute()
    {
        return 'some@mail.ru';
    }

    public static function create(array $attributes = [])
    {
        $attributes['public_id'] = Uuid::generate();
        $attributes['application_moderation_period'] = 5;
        $attributes['manage_subscription_key'] = Str::random(64);

        $model = static::query()->create($attributes);

        return $model;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function proxyLeads(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ProxyLead::class, ProxyLeadSetting::class);
    }

    /**
     * Get company with data that nesseccery for report.
     *
     * @param $id
     * @param  Carbon  $startAt
     * @param  Carbon  $endAt
     * @return Collection|Model|Company
     */
    public function getCompanyWithReportData($id, Carbon $startAt, Carbon $endAt)
    {
        return $this->with(['roistatConfig'])
            ->with(
                [
                    'roistatProxyLeads' => function ($query) use ($startAt, $endAt) {
                        $query->period(clone $startAt, clone $endAt);
                    },
                ]
            )
            ->with(
                [
                    'roistatConfig.reportLeads' => function ($query) use ($startAt, $endAt) {
                        $query->period(clone $startAt, clone $endAt);
                    },
                ]
            )
            ->with(
                [
                    'roistatConfig.approvedReports' => function ($query) use ($startAt) {
                        /* @var Builder $query */
                        $query->where('for_date', '=', $startAt->toDateString());
                    },
                ]
            )
            ->findOrFail($id);
    }

    /**
     * @return string
     */
    public function getGoogleStatusAttribute()
    {
        $this->loadMissing(['roistatConfig']);

        $hasConfig = $this->roistatConfig !== null;

        // at 6:00 Scheduler will get new google analytic
        $date = now()->hour < 6 ? now()->subDay() : now();

        /** @var \App\Domain\Roistat\Models\RoistatGoogleAnalytic $analytic */
        $analytic = $hasConfig
            ? $this->roistatConfig->mostRecentGoogleAnalytic()->whereDate('created_at', $date->toDateString())->first()
            : null;

        $hasAnalytic = $hasConfig && $analytic !== null;

        if (!$hasConfig) {
            return static::GOOGLE_NOT_CONFIGURED;
        }

        if (empty($this->roistatConfig->roistat_project_id) || empty($this->roistatConfig->api_key) || 0.0 === $this->roistatConfig->google_limit_amount) {
            return self::GOOGLE_NOT_CONFIGURED;
        }

        if (!$hasAnalytic) {
            return self::GOOGLE_HAS_ERRORS;
        }

        if (0.0 === $analytic->visitsCost) {
            return self::GOOGLE_HAS_ERRORS;
        }

        if ($analytic->visitsCost < $this->roistatConfig->google_limit_amount) {
            return self::GOOGLE_HAS_ERRORS;
        }

        if ($analytic->visitsCost >= $this->roistatConfig->google_limit_amount) {
            return self::GOOGLE_OK;
        }

        return self::GOOGLE_NOT_CONFIGURED;
    }

    public function getRoistatStatusAttribute()
    {
        $this->loadMissing('roistatConfig', 'roistatBalanceConfig.latestTransaction');

        $hasConfig = $this->roistatConfig !== null;
        $hasBalanceConfig = $this->roistatBalanceConfig !== null;
        $hasTransaction = $hasBalanceConfig && !$this->roistatBalanceConfig->latestTransaction->isEmpty();

        if (!$hasConfig) {
            return static::YANDEX_IS_NOT_CONFIGURED;
        }

        if (empty($this->roistatConfig->roistat_project_id) || empty($this->roistatConfig->api_key)) {
            return self::YANDEX_IS_NOT_CONFIGURED;
        }

        if (!$hasBalanceConfig) {
            return static::YANDEX_IS_NOT_CONFIGURED;
        }

        if (!$hasTransaction) {
            return static::YANDEX_HAS_ERRORS;
        }

        if ($this->roistatBalanceConfig->latestTransaction->first(
            )->balance < $this->roistatBalanceConfig->limit_amount) {
            return self::ROISTAT_HAS_ERRORS;
        }

        if ($this->roistatBalanceConfig->latestTransaction->first(
            )->balance >= $this->roistatBalanceConfig->limit_amount) {
            return self::ROISTAT_OK;
        }

        return self::ROISTAT_IS_NOT_OONFIGURED;
    }

    public function getYandexStatusAttribute()
    {
        $this->loadMissing(['yandexDirectConfig']);
        $yandexTodayBalance = $this->yandexLatestBalace()->whereDate('created_at', now()->toDateString())->first();

        $hasConfig = $this->yandexDirectConfig !== null;
        $hasBalance = $yandexTodayBalance !== null;

        if (!$hasConfig) {
            return self::YANDEX_IS_NOT_CONFIGURED;
        }

        if (empty($this->yandexDirectConfig->yandex_auth_key) || empty($this->yandexDirectConfig->yandex_login)) {
            return self::YANDEX_IS_NOT_CONFIGURED;
        }

        if (!$hasBalance) {
            return self::YANDEX_HAS_ERRORS;
        }

        if (0 === $yandexTodayBalance->amount) {
            return static::YANDEX_HAS_ERRORS;
        }

        if ($yandexTodayBalance->amount >= $this->yandexDirectConfig->limit_amount) {
            return self::YANDEX_OK;
        }

        if ($yandexTodayBalance->amount < $this->yandexDirectConfig->limit_amount) {
            return self::YANDEX_HAS_ERRORS;
        }

        return self::YANDEX_IS_NOT_CONFIGURED;
    }

    /** ------- HELPERS --------- */

    /**
     * Checked if company has roistat config.
     *
     * @return bool
     */
    public function hasRoistatConfig()
    {
        return $this->roistatConfig()->exists();
    }

    /** ------- END OF HELPERS --------- */
    public function yandexDirectEmailNotifications()
    {
        return $this->hasMany(EmailNotificationSetting::class)
            ->where('notification_type', 'yandex_direct')
            ->where('status', 'approved');
    }

    public function getYandexNotificationEmailsForSend()
    {
        return $this->hasMany(\App\Domain\Notification\Models\EmailNotificationSetting::class)
            ->where('notification_type', 'yandex_direct')
            ->where('status', 'approved')
            ->where('last_sent_at', '=', null);
    }

    public function roistatGoogleEmailNotifications()
    {
        return $this->hasMany(\App\Domain\Notification\Models\EmailNotificationSetting::class)
            ->where('notification_type', 'roistat_google')
            ->where('status', 'approved');
    }

    public function getGoogleNotificationEmailsForSend()
    {
        return $this->hasMany(\App\Domain\Notification\Models\EmailNotificationSetting::class)
            ->where('notification_type', 'roistat_google')
            ->where('status', 'approved')
            ->where('last_sent_at', '=', null);
    }

    /**
     * Company may has email notifications for roistat balance.
     *
     * @return mixed
     */
    public function roistatBalanceNotifications()
    {
        return $this->hasMany(EmailNotificationSetting::class)
            ->where('notification_type', '=', 'roistat_balance')
            ->where('status', 'approved');
    }

    public function getRoistatNotificationEmailsForSend()
    {
        return $this->hasMany(EmailNotificationSetting::class)
            ->where('notification_type', '=', 'roistat_balance')
            ->where('status', 'approved')
            ->where('last_sent_at', '=', null);
    }

    /**
     * Company may has emails for notifications about main problems.
     *
     * @return BelongsToMany
     */
    public function mainNotifications()
    {
        return $this->belongsToMany(User::class, 'company_role_users', 'company_id', 'user_id');
    }

    public function recipientsNotifications()
    {
        return $this->hasMany(EmailNotificationSetting::class)
            ->where('notification_type', 'proxy_leads')
            ->where('status', 'approved');
    }

    public function getApprovedNotificationsAttribute()
    {
        $emailNotificationSettings = $this->getNotificationsByType(
            \App\Domain\Notification\Models\EmailNotificationSetting::STATUS_APPROVED);
        $result = [];
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $result[] = $emailNotificationSetting->form_data;
        }

        return $result;
    }

    public function getPendingNotificationsAttribute()
    {
        $emailNotificationSettings = $this->getNotificationsByType(EmailNotificationSetting::STATUS_PENDING);
        $result = [];
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $result[] = $emailNotificationSetting->form_data;
        }

        return $result;
    }

    public function getDisabledNotificationsAttribute()
    {
        $emailNotificationSettings = $this->getNotificationsByType(
            \App\Domain\Notification\Models\EmailNotificationSetting::STATUS_DISABLED);
        $result = [];
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $result[] = $emailNotificationSetting->form_data;
        }

        return $result;
    }

    /**
     * Company may have many proxy lead goal counter data.
     *
     * @return HasMany
     */
    public function proxyLeadGoalCounters()
    {
        return $this->hasMany(\App\Domain\ProxyLead\Models\ProxyLeadGoalCounter::class);
    }

    /**
     * Company may has proxy lead settings.
     *
     * @return HasOne
     */
    public function proxyLeadSettings()
    {
        return $this->hasOne(ProxyLeadSetting::class);
    }

    /**
     * Company report notifications.
     *
     * @return mixed
     */
    public function reportNotifications()
    {
        return $this->hasMany(EmailNotificationSetting::class)
            ->where('notification_type', '=', \App\Domain\Notification\Models\EmailNotification::PROXY_LEADS)
            ->where('status', 'approved');
    }

    public function customerBalanceLimitNotifications()
    {
        return $this->hasMany(\App\Domain\Notification\Models\EmailNotificationSetting::class)
            ->where('notification_type', '=', \App\Domain\Notification\Models\EmailNotification::CUSTOMER_BALANCE)
            ->where('status', 'approved');
    }

    /**
     * Company has many emails for different notifications.
     *
     * @return HasMany
     */
    public function emailNotifications()
    {
        return $this->hasMany(\App\Domain\Notification\Models\EmailNotificationSetting::class)
            ->where('status', 'approved')
            ->orderBy('notification_type');
    }

    /**
     * Company has one configuration of zadarma service.
     *
     * @return HasOne
     */
    public function zadarmaConfig()
    {
        return $this->hasOne(\App\Domain\Zadarma\Models\ZadarmaCompanyConfig::class);
    }

    /**
     * Company has many proxy leads.
     *
     * @return HasMany
     */
    public function roistatProxyLeads()
    {
        return $this->hasMany(RoistatProxyLead::class);
    }

    public function roistatMostRecentProxyLeads()
    {
        return $this->hasMany(RoistatProxyLead::class)
            ->where('for_date', '=', Carbon::yesterday()->toDateString());
    }

    /**
     * Company has many roistat statistic data.
     *
     * @return HasMany
     */
    public function roistatStatistics()
    {
        return $this->hasMany(\App\Domain\Roistat\Models\RoistatStatistic::class);
    }

    public function roistatConfig()
    {
        return $this->hasOne(\App\Domain\Roistat\Models\RoistatCompanyConfig::class);
    }

    /**
     * Get yandex direct config.
     */
    public function yandexDirectConfig()
    {
        return $this->hasOne(YandexDirectCompanyConfig::class);
    }

    /**
     * Get yandex balances.
     *
     * @return HasMany
     */
    public function yandexBalances()
    {
        return $this->hasMany(YandexDirectBalance::class);
    }

    /**
     * Get lattest balance.
     *
     * @return mixed
     */
    public function yandexLatestBalace()
    {
        return $this->yandexBalances()->orderBy('created_at', 'desc')->take(1);
    }

    /**
     * Replacement database config.
     *
     * @return HasMany
     */
    public function replacementDatabaseConfigs()
    {
        return $this->hasMany(CompanyReplacementDatabaseConfig::class);
    }

    /**
     * Some companies can have roistat balance configuration.
     *
     * @return HasOne
     */
    public function roistatBalanceConfig()
    {
        return $this->hasOne(RcBalanceConfig::class);
    }

    /**
     * Company has many of counted costs for month.
     *
     * @return HasMany
     */
    public function totalCosts()
    {
        return $this->hasMany(TotalCompanyCost::class);
    }

    public function costsInCurrentMonth()
    {
        return $this->hasOne(TotalCompanyCost::class)
            ->whereBetween(
                'created_at',
                [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]
            );
    }

    /**
     * Company has sites.
     *
     * @return HasMany
     */
    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @param $type
     * @param $email
     * @param $repeat
     * @return bool|Model
     * @throws Exceptions\EmailSubscriptionException
     */
    public function updateOrCreateEmailNotification($type, $email, $repeat)
    {
        $changed = false;
        if (!in_array($type, EmailNotification::getListOfAvailableTypes(), true)) {
            return false;
        }

        if ($repeat) {
            $currentEmailNotificationSetting = \App\Domain\Notification\Models\EmailNotificationSetting::where(
                [
                    'company_id' => $this->id,
                    'email' => $email,
                    'notification_type' => $type,
                ]
            )->where('status', '<>', 'disabled')->count();
        } else {
            $currentEmailNotificationSetting = EmailNotificationSetting::where(
                [
                    'company_id' => $this->id,
                    'email' => $email,
                    'notification_type' => $type,
                ]
            )->count();
        }

        if (!$currentEmailNotificationSetting) {
            EmailNotificationSetting::setPending($email, $type, $this->id);
            EmailManageLink::init($email);
            $changed = true;
        }

        return $changed;
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function getProxyLeadConfig(): ?Model
    {
        $this->loadMissing('proxyLeadSettings', 'roistatConfig');

        if ($this->proxyLeadSettings !== null) {
            return $this->proxyLeadSettings;
        }

        if ($this->roistatConfig !== null) {
            return $this->roistatConfig;
        }

        return null;
    }

    public function getManagers()
    {
        $company_role_user = CompanyRoleUser::where('company_id', $this->id)->get();

        $data = [];
        if ($company_role_user) {
            foreach ($company_role_user as $value) {
                $data[] = $value->user_id;
            }
        }

        return $data;
    }

    /**
     * @param $type
     * @return EmailNotificationSetting[]|EmailNotificationSetting[][]|Builder[]|Collection|Collection[]|Model[]|mixed|null[]
     */
    private function getNotificationsByType($type)
    {
        return \App\Domain\Notification\Models\EmailNotificationSetting::where(
            [
                'company_id' => $this->id,
                'status' => $type,
            ]
        )->where('notification_type', '<>', 'main')->get();
    }

    /**
     * @param $type
     * @return EmailNotificationSetting[]|\App\Domain\Notification\Models\EmailNotificationSetting[][]|Builder[]|Collection|Collection[]|Model[]|mixed|null[]
     */
    public function getApprovedEmailsForNotificationOfType($type)
    {
        return EmailNotificationSetting::select(['email'])
            ->where(
                [
                    'company_id' => $this->id,
                    'status' => EmailNotificationSetting::STATUS_APPROVED,
                    'notification_type' => $type,
                ]
            )->pluck('email')
            ->all();
    }

    public function setNewManageKey()
    {
        $this->manage_subscription_key = Str::random(64);
        $this->save();
    }

    /**
     * @return HasMany|PaymentTransaction
     */
    public function paymentTransaction()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getDateWhenLastLeadCreated()
    {
        return $this
            ->proxyLeadSettings
            ->proxyLeads()
            ->latest('created_at')
            ->first()
            ->created_at;
    }

    public function changeEmailNotificationLastSend($type, $email)
    {
        return DB::table('email_notification_settings')
            ->where('company_id', $this->id)
            ->where('email', $email)
            ->where('notification_type', $type)
            ->where('status', EmailNotificationSetting::STATUS_APPROVED)
            ->update(['last_sent_at' => Carbon::now()]);
    }

    public function clearEmailNotificationLastSend($type)
    {
        return DB::table('email_notification_settings')
            ->where('company_id', $this->id)
            ->where('notification_type', $type)
            ->where('status', \App\Domain\Notification\Models\EmailNotificationSetting::STATUS_APPROVED)
            ->update(['last_sent_at' => null]);
    }

    public function paymentRefund($amount)
    {
        DB::table('companies')
            ->where('id', $this->id)
            ->update(['balance' => DB::raw('companies.balance +'.$amount)]);
    }

    public function paymentWriteOff($amount)
    {
        DB::table('companies')
            ->where('id', $this->id)
            ->update(['balance' => DB::raw('companies.balance -'.$amount)]);
    }

    public function shouldWeNotifyAboutMissingLeadTransaction(): bool
    {
        return $this->prepayment && !$this->free_period;
    }

    /**
     * Get current active timezone for company.
     *
     * @param Company $company
     * @return mixed|null
     */
    public function getTimezoneAttribute()
    {
        $roistatCompanyConfigs = $this->roistatConfig;

        if ($roistatCompanyConfigs === null) {
            return config('app.defaultCompanyTimezone');
        }

        return $roistatCompanyConfigs->php_timezone;
    }

    public function companyReports()
    {
        return $this->hasMany(CompanyReport::class);
    }
}
