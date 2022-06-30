<?php

namespace App\Domain\User\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use Cache;
use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustUserTrait;

/**
 * App\Domain\User\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $activated
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read Collection|Account[] $accounts
 * @property-read int|null $accounts_count
 * @property-read mixed $account
 * @property-read mixed $available_accounts
 * @property-read mixed $channels
 * @property-read mixed $companies
 * @property-read mixed $has_access_to_channels
 * @property-read mixed $has_access_to_companies
 * @property-read mixed $has_access_to_dashboard
 * @property-read mixed $has_access_to_settings
 * @property-read mixed $has_access_to_users
 * @property-read mixed $is_account_admin
 * @property-read mixed $is_admin
 * @property-read mixed $is_client
 * @property-read mixed $is_manager
 * @property-read mixed $is_super_admin
 * @property-read mixed $is_staff
 * @property-read mixed $role_name
 * @property-read Role[] $roles
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read bool $isClient
 * @property-read Company $company
 * @property-read string $timezone
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereActivated($value)
 * @method static Builder|User whereCompanyId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withRole($role)
 * @mixin Eloquent
 * @property-read int|null $roles_count
 * @method static \Database\Factories\Domain\User\Models\UserFactory factory(...$parameters)
 */
class User extends Authenticatable
{
    use Notifiable;
    use LaravelEntrustUserTrait;
    use HasFactory;

    public const GENERAL_ROLE_STAFF = 'staff';
    public const GENERAL_ROLE_CLIENT = 'client';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activated',
        'company_id',
    ];

    protected $casts = [
        'activated' => 'bool'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const ROLE_ACCOUNT_ADMIN_ID = 1;
    public const ROLE_ACCOUNT_ADMIN_NAME = 'admin';
    public const ROLE_ACCOUNT_MANAGER_ID = 2;
    public const ROLE_ACCOUNT_MANAGER_NAME = 'managers';
    public const ROLE_ACCOUNT_CLIENT_ID = 3;
    public const ROLE_ACCOUNT_CLIENT_NAME = 'сustomers';
    public const ROLE_SUPER_ADMIN_ID = 4;
    public const ROLE_SUPER_ADMIN_NAME = 'super-admin';

    public static function getPreviousAccountCookieName()
    {
        return 'prev-account-' . auth()->user()->id;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|User|null
     */
    public static function current(): ?self
    {
        return auth()->user();
    }

    //todo: remove this weird stuff
    public function getCompaniesChannel()
    {
        return Cache::store('file')->get($this->companiesChannel(), null);
    }

    public function setCompaniesChannel($channel)
    {
        Cache::store('file')->forever(
            $this->companiesChannel(),
            $channel->slug ?? $channel->id
        );

        return true;
    }

    public function companiesChannel()
    {
        return sprintf('user.%s.companies_channel', $this->id);
    }

    /**
     * Refresh companies channel.
     *
     * @param Request $request
     * @return Model|null|static
     * @throws Exception
     */
    public function refreshCompaniesChannel($request)
    {
        $hasChannel = $request->has('channel');
        $channelRequest = $request->get('channel');

        if ($hasChannel) {
            if ('_clear' === $channelRequest) {
                Cache::store('file')->forget($this->companiesChannel());

                return null;
            } else {
                if (is_numeric($channelRequest)) {
                    $channel = self::findOrFail($channelRequest);
                } else {
                    $channel = Channel::where('slug', $channelRequest)->firstOrFail();
                }

                $this->setCompaniesChannel($channel);

                return $channel;
            }
        }

        return null;
    }

    public function getRole()
    {
        return Role::leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')->where(
            'role_user.user_id',
            $this->id
        )->first();
    }

    public function getSessions()
    {
        return DB::table('sessions')->where('user_id', $this->id)->first();
    }

    public function getCurrentRole()
    {
        return Role::leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')->where(
            'role_user.user_id',
            $this->id
        )->first();
    }

    public function isCustomer(): bool
    {
        return optional($this->getCurrentRole())->name === 'customers';
    }

    public function getCompanyUser()
    {
        return $this->getCompanyForUser($this->id);
    }

    public function getCompaniesAttribute($userId)
    {
        return \App\Domain\Company\Models\Company::select('companies.*')
            ->leftJoin('company_role_users', 'companies.id', '=', 'company_role_users.company_id')
            ->where('company_role_users.user_id', $this->id)
            ->get();
    }

    public function getCompanyForUser($userId)
    {
        return \App\Domain\Company\Models\CompanyRoleUser::leftJoin(
            'companies',
            'companies.id',
            '=',
            'company_role_users.company_id'
        )
            ->where('company_role_users.user_id', $userId)
            ->selectRaw('companies.*')
            ->get();
    }

    public function getAccounts()
    {
        return Account::leftJoin('account_users', 'account_users.account_id', '=', 'accounts.id')
            ->where('account_users.user_id', $this->id)
            ->selectRaw('accounts.*')
            ->get();
    }

    public static function getPossibleAccountAdmin()
    {
        return self::query()
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->whereIn('role_user.role_id', [self::ROLE_ACCOUNT_ADMIN_ID, self::ROLE_ACCOUNT_MANAGER_ID])
            ->with(['accounts', 'roles'])
            ->orderBy('name')
            ->get();
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_users');
    }

    public function getAvailableAccountsAttribute()
    {
        return $this->isSuperAdmin ? Account::orderBy('name')->get() : $this->accounts;
    }

    public function getSettings()
    {
        $account_id = $this->getAccounts()->first()->account_id;

        return \App\Domain\YooMoney\Models\YandexSetting::where('account_id', $account_id)->first();
    }

    public function getAccountIdByCompany($company_id)
    {
        return Company::find($company_id)->account_id;
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN_NAME);
    }

    public function getIsAccountAdminAttribute()
    {
        return $this->hasRole(self::ROLE_ACCOUNT_ADMIN_NAME);
    }

    public function getIsManagerAttribute()
    {
        return $this->hasRole(self::ROLE_ACCOUNT_MANAGER_NAME);
    }

    public function getIsClientAttribute()
    {
        return $this->hasRole(self::ROLE_ACCOUNT_CLIENT_NAME);
    }

    public function getAccountAttribute()
    {
        return $this->accounts->first();
    }

    public function getRoleNameAttribute()
    {
        $role = $this->roles->first();
        if (!$role) {
            return null;
        }
        switch ($role->name) {
            case self::ROLE_SUPER_ADMIN_NAME:
                return 'Суперадмин';
            case self::ROLE_ACCOUNT_MANAGER_NAME:
                return 'Менеджер';
            case self::ROLE_ACCOUNT_CLIENT_NAME:
                return 'Клиент';
            case self::ROLE_ACCOUNT_ADMIN_NAME:
                return 'Администратор аккаунтa';
            default:
                return null;
        }
    }

    public function hasAccessToAccount($accountId)
    {
        return $this->isSuperAdmin || \App\Domain\Account\Models\AccountUser::where('account_id', $accountId)->where(
                'user_id',
                $this->id
            )->exists();
    }

    public function getHasAccessToDashboardAttribute()
    {
        return $this->isSuperAdmin || $this->isAccountAdmin;
    }

    public function getHasAccessToCompaniesAttribute()
    {
        return $this->isSuperAdmin || $this->isAdmin || $this->isManager;
    }

    public function getHasAccessToUsersAttribute()
    {
        return $this->isSuperAdmin || $this->isAccountAdmin;
    }

    public function getHasAccessToChannelsAttribute()
    {
        return $this->isSuperAdmin || $this->isAccountAdmin;
    }

    public function getHasAccessToSettingsAttribute()
    {
        return $this->isSuperAdmin || $this->isAccountAdmin;
    }

    public function getIsAdminAttribute()
    {
        return $this->isSuperAdmin || $this->isAccountAdmin;
    }

    public function getIsStaffAttribute()
    {
        return $this->isSuperAdmin
            || $this->isAccountAdmin
            || $this->isAdmin
            || $this->isManager
            || !$this->isClient;
    }

    public function getChannelsAttribute()
    {
        if ($this->isAdmin) {
            return Account::current()->channels;
        }

        $userId = $this->id;
        $accountId = Account::current()->id;

        $channelIds = DB::table('channels')
            ->select('channels.id as channelIds')
            ->join('companies', 'companies.channel_id', '=', 'channels.id')
            ->join(
                'company_role_users',
                function ($join) use ($userId, $accountId) {
                    $join->on('company_role_users.company_id', '=', 'companies.id')
                        ->where('companies.account_id', '=', $accountId)
                        ->where('company_role_users.user_id', '=', $userId);
                }
            )->get()->pluck('channelIds');

        return Channel::whereIn('id', $channelIds)->get();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getTimezoneAttribute()
    {
        return $this->company->timezone ?? config('app.defaultCompanyTimezone');
    }
}
