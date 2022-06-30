<?php

namespace App\Cabinet\Company\Controllers;

use App\Domain\Company\Models\Company;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $company = Company::with(
                'emailNotifications',
                'yandexDirectConfig',
                'roistatConfig',
                'roistatBalanceConfig',
                'mainNotifications'
            )
            ->findOrFail($id);

        $types = ['yandex_direct', 'roistat_google', 'roistat_balance'];
        $hasEmailNotifications = ! empty($company->emailNotifications);

        $info = [];
        $info['notifications'] = [];
        $info['notifications']['yandex_direct'] = [];
        $info['notifications']['roistat_google'] = [];
        $info['notifications']['roistat_balance'] = [];
        $info['notifications']['main'] = [];

        if ($hasEmailNotifications) {
            $info['notifications']['yandex_direct'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'yandex_direct' == $notification->type;
            });

            $info['notifications']['roistat_google'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'roistat_google' == $notification->type;
            });

            $info['notifications']['roistat_balance'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'roistat_balance' == $notification->type;
            });

            $info['notifications']['main'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'main' == $notification->type;
            });

            $info['notifications']['roistat_avito'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'roistat_avito' == $notification->type;
            });

            $info['notifications']['report'] = $company->emailNotifications->filter(function ($notification, $key) {
                return 'report' == $notification->type;
            });
        }

        $info['yandex_limit'] = empty($company->yandexDirectConfig) ? '0.00' : $company->yandexDirectConfig->limit_amount;
        $info['google_limit'] = empty($company->roistatConfig) ? '0.00' : $company->roistatConfig->google_limit_amount;
        $info['roistat_limit'] = empty($company->roistatBalanceConfig) ? '0.00' : $company->roistatBalanceConfig->limit_amount;

        $info['max_lead_price'] = 'Не указано';

        if (empty($company->roistatConfig)) {
            $info['max_lead_price'] = 'Не указано';
        } elseif (is_null($company->roistatConfig->max_lead_price)) {
            $info['max_lead_price'] = 'Не указано';
        } elseif (empty($company->roistatConfig->max_lead_price)) {
            $info['max_lead_price'] = '0.00 ₽';
        } else {
            $info['max_lead_price'] = $company->roistatConfig->max_lead_price.' ₽';
        }

        $info['max_costs'] = 'Не указано';

        if (empty($company->roistatConfig)) {
            $info['max_costs'] = 'Не указано';
        } elseif (is_null($company->roistatConfig->max_costs)) {
            $info['max_costs'] = 'Не указано';
        } elseif (empty($company->roistatConfig->max_costs)) {
            $info['max_costs'] = '0.00 ₽';
        } else {
            $info['max_costs'] = $company->roistatConfig->max_costs.' ₽';
        }

        $info['avito_visits_limit'] = 'Не указано';

        if (empty($company->roistatConfig)) {
            $info['avito_visits_limit'] = 'Не указано';
        } elseif (is_null($company->roistatConfig->avito_visits_limit)) {
            $info['avito_visits_limit'] = 'Не указано';
        } elseif (empty($company->roistatConfig->avito_visits_limit)) {
            $info['avito_visits_limit'] = '0';
        } else {
            $info['avito_visits_limit'] = $company->roistatConfig->avito_visits_limit;
        }

        return view('pages.company.notification.index', ['company' => $company, 'info' => $info]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
