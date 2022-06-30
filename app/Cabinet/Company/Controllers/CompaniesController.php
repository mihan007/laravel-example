<?php

namespace App\Cabinet\Company\Controllers;

use App\Cabinet\Company\Requests\CreateCompanyRequest;
use App\Domain\Account\Models\Account;
use App\Domain\Company\CompanyAnalytic;
use App\Domain\Company\CompanyUpdater;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\Company\Notification\NotificationReadableNameParser;
use App\Domain\Company\Notification\NotificationTypeParser;
use App\Domain\Company\Repositories\CompanyReportRepository;
use App\Domain\Roistat\Jobs\CheckRoistatAnalyticsDimensionsValuesAsync;
use App\Domain\User\Models\User;
use App\Support\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class CompaniesController extends Controller
{
    // order of action should be based on https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller
    public function index(Request $request)
    {
        $channels = User::current()->channels;
        $companies = Account::current()->companies;

        if (User::current()->is_admin || User::current()->is_super_admin) {
            $managers = Account::current()->managers;
        }

        return view('company.index')->with(
            'data',
            [
                'channels' => $channels,
                'companies_count' => count($companies),
                'managers' => $managers ?? [],
                'managerId' => $request->managerId,
                'channelId' => $request->channelId,
            ]
        );
    }

    public function create()
    {
        $channels = User::current()->channels;
        if (User::current()->is_admin || User::current()->is_super_admin) {
            $managers = Account::current()->managers;
        }

        return view(
            'pages.companies-add',
            [
                'account' => Account::current(),
                'user' => User::current(),
                'managers' => $managers ?? [],
                'channels' => $channels ?? []
            ]
        );
    }

    public function store(CreateCompanyRequest $request)
    {
        $attributes = $request->all();
        $attributes['prepayment'] = true;
        $attributes['free_period'] = false;
        $attributes['account_id'] = Account::current()->id;

        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::create($attributes);

        $company->roistatConfig()->create(
            ['max_lead_price' => $attributes['roistat_config']['max_lead_price'] ?? null]
        );

        /** @var User $user */
        $user = Auth::user();
        if ($user->is_manager || $user->is_admin || $user->is_super_admin) {
            $company_role_user = new CompanyRoleUser();
            $company_role_user->user_id = Auth::id();
            $company_role_user->company_id = $company->id;
            $company_role_user->save();
        }

        if (!empty($request->get('role_users'))) {
            foreach ($request->get('role_users') as $user_id) {
                $company_role_user = new CompanyRoleUser();
                $company_role_user->user_id = $user_id;
                $company_role_user->company_id = $company->id;
                $company_role_user->save();
            }
        }


        return response()->json(
            [
                'status' => 'success',
                'redirect' => route('account.companies.edit', ['company' => $company->id])
            ]
        );
    }

    public function show($accountId, $id)
    {
        $company = Company::with(
            [
                'roistatConfig',
                'yandexDirectConfig',
                'roistatMostRecentProxyLeads',
                'roistatStatistics',
                'roistatConfig.mostRecentAnalytic',
                'roistatConfig.yesterdayAnalytic',
                'roistatConfig.mostRecentGoogleAnalytic',
                'roistatConfig.dimensionsValues',
                'roistatConfig.avitoYesterdayAnalytic',
                'yandexDirectEmailNotifications',
                'roistatGoogleEmailNotifications',
                'roistatBalanceConfig',
                'roistatBalanceNotifications',
                'costsInCurrentMonth',
                'notifications' => function ($query) {
                    $query->limit(3);
                },
            ]
        )
            ->findOrFail($id);

        $info = [];

        /* YESTERDAY ANALYTIC */

        $info['yesterdayAnalytic'] = [
            'visit_count' => 'Нет данных',
            'visits_to_leads' => 'Нет данных',
            'lead_count' => 'Нет данных',
            'visits_cost' => 'Нет данных',
            'cost_per_click' => 'Нет данных',
            'cost_per_lead' => 'Нет данных',
            'for_date' => 'Нет данных',
        ];

        $companyAnalytic = new CompanyAnalytic($company);
        $analytic = $companyAnalytic->get();

        if ($analytic !== false) {
            $info['yesterdayAnalytic'] = [
                'visit_count' => $analytic['visit_count'],
                'visits_to_leads' => round($analytic['visits_to_leads'], 2),
                'lead_count' => $analytic['lead_count'],
                'visits_cost' => round($analytic['visits_cost'], 2),
                'cost_per_click' => round($analytic['cost_per_click'], 2),
                'cost_per_lead' => round($analytic['cost_per_lead'], 2),
                'for_date' => $analytic['for_date'],
            ];
        }

        /* END OD YESTERDAY ANALYTIC */

        $info['yesterdayLeads'] = 0;

        if (empty($company->roistatConfig) || empty($company->roistatConfig->mostRecentAnalytic)) {
            $info['yesterdayLeads'] = 0;
        } else {
            $info['yesterdayLeads'] = intval($company->roistatConfig->mostRecentAnalytic->visitCount);
        }

        $info['monthCosts'] = '0.00';

        if (empty($company->costsInCurrentMonth)) {
            $info['monthCosts'] = '0.00';
        } else {
            $info['monthCosts'] = $company->costsInCurrentMonth->amount;
        }

        $info['avito_yesterday_analytic'] = [
            'visit_count' => 'Нет данных',
            'visits_to_leads' => 'Нет данных',
            'lead_count' => 'Нет данных',
            'visits_cost' => 'Нет данных',
            'cost_per_click' => 'Нет данных',
            'cost_per_lead' => 'Нет данных',
            'for_date' => 'Нет данных',
        ];

        if (empty($company->roistatConfig) || empty($company->roistatConfig->avitoYesterdayAnalytic)) {
        } else {
            $info['avito_yesterday_analytic'] = [
                'visit_count' => $company->roistatConfig->avitoYesterdayAnalytic->visit_count,
                'visits_to_leads' => round($company->roistatConfig->avitoYesterdayAnalytic->visits_to_leads, 2),
                'lead_count' => $company->roistatConfig->avitoYesterdayAnalytic->lead_count,
                'visits_cost' => round($company->roistatConfig->avitoYesterdayAnalytic->visits_cost, 2),
                'cost_per_click' => round($company->roistatConfig->avitoYesterdayAnalytic->cost_per_click, 2),
                'cost_per_lead' => round($company->roistatConfig->avitoYesterdayAnalytic->cost_per_lead, 2),
                'for_date' => $company->roistatConfig->avitoYesterdayAnalytic->for_date,
            ];
        }

        $info['monthCosts'] = '0.00';

        if (empty($company->costsInCurrentMonth)) {
            $info['monthCosts'] = '0.00';
        } else {
            $info['monthCosts'] = $company->costsInCurrentMonth->amount;
        }

        $roistatRadableTimezone = $this->getReadableTimezone(
            isset($company->roistatConfig) ? $company->roistatConfig->timezone : ''
        );

        $info['notifications'] = [];

        if (!empty($company->notifications)) {
            Carbon::setLocale('ru');

            foreach ($company->notifications as $notification) {
                $notificationParser = new NotificationTypeParser($notification);
                $notificationType = $notificationParser->getType();

                $name = (new NotificationReadableNameParser($notification))->getName();

                if (false === $notificationType) {
                    continue;
                }

                $info['notifications'][] = [
                    'name' => false === $name ? 'Уведомление' : $name,
                    'type' => $notificationType,
                    'message' => $notification->data['message'],
                    'date' => Carbon::parse($notification->created_at)->diffForHumans(),
                    'ajax-date' => Carbon::parse($notification->created_at)->timestamp,
                ];
            }
        }

        $info['notifications-amount'] = count($info['notifications']);

        return view(
            'pages.companies-show',
            [
                'company' => $company,
                'roistatReadableTimezone' => $roistatRadableTimezone,
                'info' => $info,
            ]
        );
    }

    public function edit(Request $request, $accountId, $id)
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::with(
            [
                'roistatConfig',
                'yandexDirectConfig',
                'zadarmaConfig',
                'yandexDirectEmailNotifications',
                'roistatGoogleEmailNotifications',
                'roistatBalanceConfig',
                'roistatBalanceNotifications',
                'mainNotifications',
                'recipientsNotifications',
                'sites'
            ]
        )->findOrFail($id);

        $channels = User::current()->channels;
        if (User::current()->is_admin || User::current()->is_super_admin) {
            $managers = Account::current()->managers;
            $roleUsers =  $company->getManagers();
        }

        if ($request->ajax()) {
            return [
                'company' => $company,
                'channels' => $channels ?? [],
                'managers' => $managers ?? [],
                'role_users' => $roleUsers ?? []
            ];
        }

        return view(
            'company.edit',
            [
                'company' => $company,
                'channels' => $channels ?? [],
                'managers' => $managers ?? [],
                'manage_subscription_link' => route(
                    'subscription.company.admin',
                    ['key' => $company->manage_subscription_key]
                ),
            ]
        );
    }

    public function update(CreateCompanyRequest $request, $accountId, $id)
    {
        $company = Company::findOrFail($id);
        $companyUpdater = new CompanyUpdater($company, $request);
        $companyUpdater->update();

        CheckRoistatAnalyticsDimensionsValuesAsync::dispatch($id);

        return response()->json(
            [
                'status' => 'success'
            ]
        );
    }

    public function destroy($accountId, $id)
    {
        $company = Company::findOrFail($id);
        CompanyRoleUser::where('company_id', $id)->delete();
        $company->delete();

        return redirect()->route('account.companies.index')->with(
            'message',
            [
                'status' => 'success',
                'text' => "Компания {$company->name} успешно удалена",
            ]
        );
    }

    public function ajaxUpdate(Request $request)
    {
        Artisan::call('report:update');
        $updated_at = CompanyReport::max('updated_at');

        return response()->json(['updated_at' => $updated_at], 200);
    }

    public function ajaxList(Request $request)
    {
        return (new CompanyReportRepository($request, Auth::user()))->getAndPaginate();
    }

    /**
     * Get yandex balances for certain period.
     *
     * @param $id
     * @return JsonResponse
     */
    public function ajaxYandexBalanceForPeriod($accountId, $id)
    {
        $company = Company::findOrFail($id);
        $userPeriod = empty($_POST['period']) ? '-3 days' : $_POST['period'];
        $time = new Carbon($userPeriod);
        $balances = $company->yandexBalances()->where('created_at', '>=', $time->toDateString())->get();

        return response()->json($balances);
    }

    public function ajaxNotifications($id, Request $request)
    {
        $company = Company::findOrFail($id);

        if (!$request->has('createdAt') || 0 == $request->get('createdAt')) {
            return response()->json(['status' => 'success', 'notifications' => []]);
        }

        /** @var Collection $notifications */
        $notifications = $company->notifications()
            ->where('created_at', '<', Carbon::createFromTimestamp($request->get('createdAt'))->toDateTimeString())
            ->limit(3)
            ->get();

        if (empty($notifications) || $notifications->isEmpty()) {
            return response()->json(['status' => 'success', 'notifications' => []]);
        }

        $parsedNotifications = [];

        foreach ($notifications as $notification) {
            $notificationParser = new NotificationTypeParser($notification);
            $notificationType = $notificationParser->getType();

            $name = (new NotificationReadableNameParser($notification))->getName();

            if (false === $notificationType) {
                continue;
            }

            $parsedNotifications[] = [
                'name' => false === $name ? 'Уведомление' : $name,
                'type' => $notificationType,
                'message' => $notification->data['message'],
                'date' => Carbon::parse($notification->created_at)->diffForHumans(),
                'ajax-date' => Carbon::parse($notification->created_at)->timestamp,
            ];
        }

        return response()->json(
            [
                'status' => 'success',
                'notifications' => $parsedNotifications,
            ]
        );
    }

    /**
     * Return readable timezone.
     *
     * @param $time
     * @return string
     */
    protected function getReadableTimezone($time)
    {
        $readableTimezone = '';

        switch ($time) {
            case '+0200':
                $readableTimezone = 'UTC+02:00 (Калининградское время)';
                break;
            case '+0300':
                $readableTimezone = 'UTC+03:00 (Московское время)';
                break;
            case '+0400':
                $readableTimezone = 'UTC+04:00 (Самарское время)';
                break;
            case '+0500':
                $readableTimezone = 'UTC+05:00 (Екатеринбургское время)';
                break;
            case '+0600':
                $readableTimezone = 'UTC+06:00 (Омское время)';
                break;
            case '+0700':
                $readableTimezone = 'UTC+07:00 (Красноярское время)';
                break;
            case '+0800':
                $readableTimezone = 'UTC+08:00 (Иркутское время)';
                break;
            case '+0900':
                $readableTimezone = 'UTC+09:00 (Якутское время)';
                break;
            case '+1000':
                $readableTimezone = 'UTC+10:00 (Владивостокское время)';
                break;
            case '+1100':
                $readableTimezone = 'UTC+11:00 (Среднеколымское время)';
                break;
            default:
                $readableTimezone = 'Не определено';
                break;
        }

        return $readableTimezone;
    }
}
