<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 30.09.2017
 * Time: 19:00.
 */

namespace App\Domain\Company;

use App\Cabinet\Company\Requests\CreateCompanyRequest;
use App\Domain\Account\Models\AccountUser;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Validator;

class CompanyUpdater
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;

    /**
     * @var CreateCompanyRequest
     */
    private $request;

    /**
     * CompanyUpdater constructor.
     * @param $company
     * @param  \App\Cabinet\Company\Requests\CreateCompanyRequest  $request
     */
    public function __construct($company, CreateCompanyRequest $request)
    {
        $this->company = $company;
        $this->request = $request;
    }

    public function update()
    {
        $userData = $this->getParsedRequestData();

        $this->updateCompany($userData);
        $this->updateCompanyRoles($userData);
        $this->updateRoistatInformation($userData['roistat_config']);
//        $this->updateYandexDirectInformation($userData);

//        $this->updateFilters($userData);
//        $this->updateGoogleAnalyticFilters($userData);
//        $this->updateRoistatBalanceInformation($userData);
//        $this->updateNotifications($userData);
//        $this->updateSites($userData);
//        $this->updateZadarmaInformation($userData);

//        $this->updateAccessPanel($userData);

        return true;
    }

    /**
     * Prepare user input.
     *
     * @return array
     */
    protected function getParsedRequestData()
    {
        $result = [];

        $requestData = $this->request->all();

        // for company
        $result['name'] = $requestData['name'];
        $result['channel_id'] = $requestData['channel_id'];
        $result['application_moderation_period'] = $requestData['application_moderation_period'] ?? null;
        $result['lead_cost'] = $requestData['lead_cost'] ?? null;
        $result['balance_limit'] = $requestData['balance_limit'] ?? null;
        $result['approve_description'] = $requestData['approve_description'] ?? null;
        $result['amount_limit'] = $requestData['amount_limit'] ?? 0;

        //for roistat
        $result['roistat_config']['max_lead_price'] = $requestData['roistat_config']['max_lead_price'] ?? null;
        $result['roistat_config']['max_costs'] = $requestData['roistat_config']['max_lead_price'] ?? null;
        $result['roistat_config']['project_id'] = $requestData['roistat_config']['project_id'] ?? null;
        $result['roistat_config']['api_key'] = $requestData['roistat_config']['api_key'] ?? null;
        $result['roistat_config']['timezone'] = $requestData['roistat_config']['timezone'] ?? null;

        $result['roistat_config']['limit'] = isset($requestData['roistat_config']['limit']) ? $requestData['roistat_config']['limit'] : null;
        $result['roistat_config']['avito_visits_limit'] = isset($requestData['roistat_config']['avito_visits_limit']) ? $requestData['roistat_config']['avito_visits_limit'] : null;


        // role managers
        $result['role_users'] = !empty($requestData['role_users']) ? $requestData['role_users'] : [];

//        $result['lead_cost'] = (int) $requestData['lead_cost'];
//        $result['check_for_graph'] = empty($requestData['check_for_graph']) ? 0 : $requestData['check_for_graph'];
//
//        $result['description'] = empty($requestData['description']) ? null : $requestData['description'];
//        $result['channel_id'] = empty($requestData['channel_id']) || 'null' === $requestData['channel_id'] ? null : $requestData['channel_id'];
//        $result['prepayment'] = empty($requestData['prepayment']) ? 0 : $requestData['prepayment'];
//        $result['free_period'] = empty($requestData['free_period']) ? 0 : $requestData['free_period'];
//        $result['balance_limit'] = empty($requestData['balance_limit']) ? 0 : $requestData['balance_limit'];
//        $result['application_moderation_period'] = empty($requestData['application_moderation_period']) ? 0 : $requestData['application_moderation_period'];
//        $result['approve_description'] = empty($requestData['approve_description']) ? 0 : $requestData['approve_description'];
//
//        // for yandex config
//        $result['ya_login'] = empty($requestData['ya_login']) ? null : $requestData['ya_login'];
//        $result['ya_limit'] = empty($requestData['ya_limit']) ? null : $requestData['ya_limit'];
//
//        // for panel
//        $result['access_panel'] = empty($requestData['access_panel']) ? [] : $requestData['access_panel'];
//
//        // for roistat config

//
//        $result['roistat_limit'] = is_null(
//            $requestData['roistat_limit']
//        ) ? null : (0 == $requestData['roistat_limit'] ? 0 : $requestData['roistat_limit']);
//        $result['roistat_max_lead_price'] = is_null($requestData['roistat_max_lead_price']) ? null : round(
//            floatval($requestData['roistat_max_lead_price']),
//            2
//        );
//        $result['roistat_max_costs'] = is_null($requestData['roistat_max_costs']) ? null : round(
//            floatval($requestData['roistat_max_costs']),
//            2
//        );
//        $result['roistat_avito_visits_limit'] = is_null($requestData['roistat_avito_visits_limit']) ? null : intval(
//            $requestData['roistat_avito_visits_limit']
//        );
//
//        // for filters
//        $result['roistat_filters'] = empty($requestData['roistat_filters']) ? [] : $requestData['roistat_filters'];
//
//        // for google analytic
//        $result['roistat_google_filters'] = empty($requestData['roistat_google_filters']) ? [] : $requestData['roistat_google_filters'];
//
//        // for roistat balance
//        $result['roistat_balance_project_id'] = empty($requestData['roistat_balance_project_id']) ? null : $requestData['roistat_balance_project_id'];
//        $result['roistat_balance_api_key'] = empty($requestData['roistat_balance_api_key']) ? null : $requestData['roistat_balance_api_key'];
//        $result['roistat_balance_limit'] = empty($requestData['roistat_balance_limit']) ? null : $requestData['roistat_balance_limit'];
//
//        // for sms ru
//        $result['sms_ru_api_id'] = empty($requestData['sms_ru_api_id']) ? null : $requestData['sms_ru_api_id'];
//
//        // for zadarma
//        $result['zadarma_key'] = empty($requestData['zadarma_key']) ? null : $requestData['zadarma_key'];
//        $result['zadarma_secret'] = empty($requestData['zadarma_secret']) ? null : $requestData['zadarma_secret'];
//
//        // for notifications
//        $result['notifications'] = empty($requestData['notifications']) ? [] : $requestData['notifications'];
//        $result['notification_admin'] = $requestData['notification_admin'] ?? [];
//
//        // for site
//        $result['site'] = empty($requestData['site']) ? [] : $requestData['site'];



        return $result;
    }

    /**
     * Update company information.
     *
     * @param $data
     * @return bool
     */
    protected function updateCompany($data)
    {
        return $this->company->update($data);
    }

    /**
     * Update filters.
     *
     * @param $data
     * @return bool
     */
    protected function updateFilters($data)
    {
        $userFilters = $data['roistat_filters'];
        $roistatConfig = $this->company->roistatConfig;

        // update analytic filters
        if (!empty($userFilters) && !empty($roistatConfig)) {
            $roistatFilters = $roistatConfig->dimensionsValues()->get();

            $checkedFilters = [];

            if (!empty($roistatFilters)) {
                foreach ($roistatFilters as $filter) {
                    if (in_array($filter->id, $userFilters)) {
                        $checkedFilters[] = $filter->id;
                    }
                }
            }

            if (!empty($checkedFilters)) {
                $roistatConfig->dimensionsValues()->whereIn('id', $checkedFilters)->update(['is_active' => 1]);
                $roistatConfig->dimensionsValues()->whereNotIn('id', $checkedFilters)->update(['is_active' => 0]);
            }
        }

        return true;
    }

    /**
     * Update google analytic filter.
     *
     * @param $data
     * @return bool
     */
    protected function updateGoogleAnalyticFilters($data)
    {
        $userFilters = $data['roistat_google_filters'];
        $roistatConfig = $this->company->roistatConfig;

        // update google analytic filters
        if (!empty($userFilters) && !empty($roistatConfig)) {
            $roistatFilters = $roistatConfig->dimensionsValues()->get();

            $checkedFilters = [];

            if (!empty($roistatFilters)) {
                foreach ($roistatFilters as $filter) {
                    if (in_array($filter->id, $userFilters)) {
                        $checkedFilters[] = $filter->id;
                    }
                }
            }

            if (!empty($checkedFilters)) {
                $roistatConfig->dimensionsValues()->whereIn('id', $checkedFilters)->update(['is_google_active' => 1]);
                $roistatConfig->dimensionsValues()->whereNotIn('id', $checkedFilters)->update(
                    ['is_google_active' => 0]
                );
            }
        }

        return true;
    }

    /**
     * Update or create roistat balance configuration.
     *
     * @param $data
     * @return bool
     */
    protected function updateRoistatBalanceInformation($data)
    {
        $roistatBalanceConfig = $this->company->roistatBalanceConfig()->first();

        $roistatBalanceConfigRequest = array_filter(
            $data,
            function ($value, $key) {
                return in_array(
                    $key,
                    ['roistat_balance_project_id', 'roistat_balance_api_key', 'roistat_balance_limit']
                );
            },
            ARRAY_FILTER_USE_BOTH
        );

        // store differences of names (aliases)
        $roistatBalanceConfigMap = [
            'roistat_balance_project_id' => 'project_id',
            'roistat_balance_api_key' => 'api_key',
            'roistat_balance_limit' => 'limit_amount',
        ];

        foreach ($roistatBalanceConfigRequest as $key => $value) {
            // set correct name of variable
            if (isset($roistatBalanceConfigRequest[$key])) {
                $roistatBalanceConfigRequest[$roistatBalanceConfigMap[$key]] = $value;
            }
        }

        if (empty($roistatBalanceConfig)) {
            return (bool)$this->company->roistatBalanceConfig()->create($roistatBalanceConfigRequest);
        } else {
            return $roistatBalanceConfig->update($roistatBalanceConfigRequest);
        }

        return true;
    }

    /**
     * Update or create roistat config.
     *
     * @param $data
     * @return bool
     */
    protected function updateRoistatInformation($data)
    {
        $userRoistatConfigData = [
            'roistat_project_id' => $data['project_id'] ?? '',
            'api_key' => $data['api_key'] ?? '',
            'timezone' => $data['timezone'] ?? '',
            'google_limit_amount' => empty($data['roistat_limit']) ? 0 : (float)$data['limit'],
            'max_lead_price' => $data['max_lead_price'] ?? null,
            'max_costs' => $data['max_costs'] ?? null,
            'avito_visits_limit' => $data['avito_visits_limit'] ?? null,
        ];

        if (!$roistatConfig = $this->company->roistatConfig()->first()){
            $this->company->roistatConfig()->create($userRoistatConfigData);
            Artisan::call('roistat:analyticsDimensionsValues');
            return true;
        }

        $roistatConfig->update($userRoistatConfigData);
        return true;
    }

    /**
     * Update notifications recipients.
     *
     * @param $data
     * @return bool
     */
    protected function updateNotifications($data)
    {
        $companyNotificationUpdater = new CompanyNotificationsUpdater($this->company);

        return $companyNotificationUpdater->update($data['notifications'], $data['notification_admin']);
    }

    protected function updateSites($data)
    {
        $sites = collect($data['site']);
        $ids = [];

        $newSites = $sites->filter(
            function ($item, $key) {
                return 'new' === $item['id'];
            }
        );

        foreach ($newSites as $site) {
            $ids[] = $this->company->sites()->create($site)->id;
        }

        $updateSites = $sites->filter(
            function ($item, $key) {
                return 'new' !== $item['id'];
            }
        );

        foreach ($updateSites as $site) {
            $dbSite = $this->company->sites()->where('id', '=', $site['id'])->first();

            if (empty($dbSite)) {
                continue;
            }

            if ($dbSite->url === $site['url']) {
                $ids[] = $site['id'];
                continue;
            }

            $ids[] = $site['id'];

            $dbSite->url = $site['url'];
            $dbSite->save();
        }

        if (empty($ids)) {
            $this->company->sites()->delete();

            return true;
        }

        $this->company->sites()->whereNotIn('id', $ids)->delete();

        return true;
    }

    /**
     * Update or create Zadarma configuration.
     *
     * @param $data
     * @return bool
     */
    protected function updateZadarmaInformation($data)
    {
        $zadarmaConfig = $this->company->zadarmaConfig()->first();
        $result = true;

        foreach (['key', 'secret'] as $varName) {
            $var = $data["zadarma_{$varName}"];

            if (empty($zadarmaConfig) && !empty($var)) {
                $zadarmaConfig = $this->company->zadarmaConfig()->create([$varName => $var]);
                $result = $result && ((bool)$zadarmaConfig);
            } elseif (!empty($zadarmaConfig) && empty($var)) {
                $zadarmaConfig->{$varName} = '';
                $result = $result && $zadarmaConfig->save();
            } elseif (!empty($zadarmaConfig) && !empty($var) && $var != $zadarmaConfig->{$varName}) {
                $zadarmaConfig->{$varName} = $var;
                $result = $result && $zadarmaConfig->save();
            }
        }

        return $result;
    }

    /**
     * Update or create yandex config.
     *
     * @param $data
     * @return bool
     */
    protected function updateYandexDirectInformation($data)
    {
        $yandexDirectConfig = $this->company->yandexDirectConfig()->first();
        $isNoYandexConfig = empty($yandexDirectConfig);
        $yandexLogin = trim((string)$data['ya_login']);
        $isNoRequestLogin = empty($yandexLogin);
        $yandexLimit = $data['ya_limit'];

        // if there is no yandex direct config and there is a yandex login in reqest
        // add new config
        if ($isNoYandexConfig && !$isNoRequestLogin) {
            return (bool)$this->company->yandexDirectConfig()->create(
                [
                    'yandex_login' => $yandexLogin,
                ]
            );
            // if there is yandex direct config and request login different with database yandex login
        } else {
            if (!$isNoYandexConfig && !$isNoRequestLogin && $yandexLogin != $yandexDirectConfig->yandex_login) {
                $yandexDirectConfig->yandex_login = $yandexLogin;
                $yandexDirectConfig->yandex_auth_key = ''; // сбрасываем ключ
                $yandexDirectConfig->token_life_time = 0;
                $yandexDirectConfig->limit_amount = $yandexLimit;

                return $yandexDirectConfig->save();
            } elseif (!$isNoYandexConfig) {
                $yandexDirectConfig->limit_amount = $yandexLimit;

                return $yandexDirectConfig->save();
            }
        }

        return true;
    }


    /**
     * @param $data
     * @return bool|void
     * @throws \App\Exceptions\EmailSubscriptionException
     */
    protected function updateCompanyRoles($data)
    {
        $managers = $data['role_users'];

        if (!$managers) {
            return;
        }

        CompanyRoleUser::where('company_id', $this->company->id)->delete();
        EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('notification_type', 'main')
            ->delete();

        foreach ($managers as $manager) {
            $company_role_users = new CompanyRoleUser();
            $company_role_users->company_id = $this->company->id;
            $company_role_users->user_id = $manager;
            $company_role_users->save();

            $user = User::find($manager);

            EmailNotificationSetting::setApproved($user->email, 'main', $this->company->id);
        }

        return true;
    }

    protected function updateAccessPanel($data)
    {
        $emails = [];

        foreach ($data['access_panel'] as $data) {
            $emails[] = $data['panel_email'];
            if (isset($data['panel_email'], $data['panel_password']) && $data['panel_email'] && $data['panel_password']) {
                $user = User::where('email', $data['panel_email'])->first();
                $validator = Validator::make(
                    [
                        'email' => $data['panel_email'],
                        'password' => $data['panel_password'],
                    ],
                    [
                        'password' => 'required|min:6',
                        'email' => $user ? 'required|email|max:255' : 'required|email|max:255|unique:users,email',
                    ]
                );

                $userIsAdmin = $user && $user->getRole()->name === 'admin';
                $userIsSuperadmin = $user && $user->getRole()->name === 'super-admin';
                if ($user && !$userIsAdmin && !$userIsSuperadmin && !$validator->fails()) {
                    $user->password = Hash::make($data['panel_password']);
                    $user->company_id = $this->company->id;
                    $user->save();

                    AccountUser::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'account_id' => $this->company->account_id,
                        ],
                        [
                            'role' => $user->getRole()->name,
                        ]
                    );

                    if ($user->getSessions()) {
                        session()->getHandler()->destroy($user->getSessions()->id);
                    }
                } elseif (!$validator->fails() && !$user) {
                    $user = User::create(
                        [
                            'name' => $this->company->name,
                            'email' => $data['panel_email'],
                            'password' => Hash::make($data['panel_password']),
                            'activated' => '1',
                            'company_id' => $this->company->id,
                        ]
                    );

                    AccountUser::create(
                        [
                            'user_id' => $user->id,
                            'account_id' => $this->company->account_id,
                            'role' => 'сustomers',
                        ]
                    );

                    $role = Role::where('name', 'сustomers')->firstOrFail();
                    $user->roles()->attach($role->id);
                } elseif ($user && !$validator->fails()) {
                    AccountUser::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'account_id' => $this->company->account_id,
                        ],
                        [
                            'role' => $user->getRole()->name,
                        ]
                    );
                }
            }
        }

        User::where('company_id', $this->company->id)
            ->whereNotIn('email', $emails)
            ->update(['company_id' => null]);

        return true;
    }
}
