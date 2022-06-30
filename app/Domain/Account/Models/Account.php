<?php

namespace App\Domain\Account\Models;

use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Tinkoff\Models\TinkoffSetting;
use App\Domain\User\Models\User;
use App\Domain\YooMoney\Models\YandexSetting;
use App\Domain\Company\Models\CompanyReport;
use Carbon\Carbon;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * App\Domain\Account\Models\Account
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read AboutCompany|null $aboutCompany
 * @property-read AccountSetting|null $accountSetting
 * @property-read Collection|Channel[] $channels
 * @property-read int|null $channels_count
 * @property-read Collection|\App\Domain\Company\Models\Company[] $companies
 * @property-read int|null $companies_count
 * @property-read mixed $admin
 * @property-read mixed $count_all_leads
 * @property-read mixed $managers
 * @property-read mixed $users
 * @property-read mixed $warning_email
 * @property-read \App\Domain\Tinkoff\Models\TinkoffSetting|null $tinkoffSetting
 * @property-read \App\Domain\YooMoney\Models\YandexSetting|null $yandexSetting
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static Builder|Account onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static Builder|Account withTrashed()
 * @method static Builder|Account withoutTrashed()
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Account\Models\AccountFactory factory(...$parameters)
 */
class Account extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
    ];

    /**
     * @param bool $abortIfNotFound
     * @return Account
     */
    public static function current($abortIfNotFound = true): ?self
    {
        $currentAccount = config('app.accountId');
        if (! $currentAccount) {
            return null;
        }

        $account = self::find($currentAccount);
        if ($abortIfNotFound && ! $account) {
            abort(404);
        }

        return $account;
    }

    public function getUsers()
    {
        return User::join('account_users', 'users.id', '=', 'account_users.user_id')
            ->where('account_users.account_id', '=', $this->id)->select('users.*')->get();
    }

    public function getManagersAttribute()
    {
        return User::join('account_users', 'users.id', '=', 'account_users.user_id')
            ->leftJoin('role_user', 'role_user.user_id', '=', 'account_users.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.name', ['managers', 'admin'])
            ->where('account_users.account_id', '=', $this->id)
            ->orderBy('users.name')
            ->select('users.*')->get();
    }

    public function getUsersAttribute()
    {
        return User::join('account_users', 'users.id', '=', 'account_users.user_id')
            ->where('account_users.account_id', '=', $this->id)
            ->select('users.*')
            ->get();
    }

    public function getAdminAttribute()
    {
        return User::join('account_users', 'users.id', '=', 'account_users.user_id')
            ->where('account_users.account_id', '=', $this->id)
            ->where('role', '=', User::ROLE_ACCOUNT_ADMIN_NAME)
            ->select('users.*')
            ->first();
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function companyReports()
    {
        return $this->hasMany(CompanyReport::class);
    }

    public function channels()
    {
        return $this->hasMany(Channel::class)->orderBy('name');
    }

    public function yandexSetting()
    {
        return $this->hasOne(YandexSetting::class);
    }

    public function tinkoffSetting()
    {
        return $this->hasOne(TinkoffSetting::class);
    }

    public function accountSetting()
    {
        return $this->hasOne(AccountSetting::class);
    }

    public function aboutCompany()
    {
        return $this->hasOne(AboutCompany::class);
    }

    public function countLeadsForPeriod($from, $to)
    {
        $startAt = Carbon::parse($from)->toDateTimeString();
        $endAt = Carbon::parse($to)->toDateTimeString();
        $results = DB::select(
            'SELECT COUNT(pl.id) as leadCount
                            FROM pl_report_leads prl
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            JOIN companies ON pls.company_id = companies.id
                            AND companies.account_id = :account_id
                            AND prl.company_confirmed = 1
                            AND prl.created_at BETWEEN :start_at AND :end_at
                            AND pl.deleted_at IS NULL',
            [
                'account_id' => $this->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]
        );

        return $results[0]->leadCount ?? 0;
    }

    public function getCountAllLeadsAttribute()
    {
        return $this->countLeadsForPeriod('2000-01-01', '2100-01-01');
    }

    public function isActiveRs()
    {
        $rs = $this->accountSetting;

        return $rs && $rs->is_active;
    }

    public function isActiveBankCard()
    {
        $ys = $this->yandexSetting;

        return $ys && $ys->is_active && $ys->is_bank_card;
    }

    public function isActiveYandexMoney()
    {
        $ys = $this->yandexSetting;

        return $ys && $ys->is_active && $ys->is_yandex_wallet;
    }

    public function isPaymentPossible()
    {
        return $this->isActiveBankCard() || $this->isActiveYandexMoney() || $this->isActiveRs();
    }

    public function getWarningEmailAttribute()
    {
        return $this->admin ? $this->admin->email : env('EMAIL_ADMIN');
    }
}
