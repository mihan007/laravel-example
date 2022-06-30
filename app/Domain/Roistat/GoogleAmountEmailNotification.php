<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 16.05.2017
 * Time: 15:01.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\RoistatGoogleNotification;
use Illuminate\Support\Facades\Mail;

class GoogleAmountEmailNotification
{
    public function __construct()
    {
    }

    public function check()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // какая то дичь - если просто вытянуть компании с зависимостями, через get(), то mostRecentGoogleAnalytic будет
            // пустым, если вытянуть с зависимостями для нужной компании через find() то все ок
            $company->loadMissing(
                ['roistatConfig', 'roistatConfig.mostRecentGoogleAnalytic', 'roistatGoogleEmailNotifications']
            );

            if (! $company->roistatBalanceConfig) {
                continue;
            }

            $lastTransaction = $company->roistatBalanceConfig->latestTransaction->first();
            if (! $lastTransaction) {
                continue;
            }

            if (! ($lastTransaction->balance <= $company->roistatBalanceConfig->limit_amount)) {
                continue;
            }

            if (empty($company->roistatGoogleEmailNotifications)) {
                Mail::send(
                    new RoistatGoogleNotification($company, $company->roistatConfig->mostRecentGoogleAnalytic->first())
                );
                continue;
            }

            if (
                empty($company->roistatConfig) ||
                empty($company->roistatConfig->roistat_project_id) ||
                empty($company->roistatConfig->api_key)
            ) {
                continue;
            }

            if ($company->roistatConfig->mostRecentGoogleAnalytic->isEmpty()) {
                continue;
            }

            if (! ($company->roistatConfig->mostRecentGoogleAnalytic->first(
                )->visitsCost <= $company->roistatConfig->google_limit_amount)) {
                continue;
            }

            // if current balance lower limit - send notification
            foreach ($company->getGoogleNotificationEmailsForSend as $emailNotification) {
                Mail::send(
                    new RoistatGoogleNotification(
                        $company,
                        $company->roistatConfig->mostRecentGoogleAnalytic->first(),
                        $emailNotification->email
                    )
                );
            }
        }

        return true;
    }
}
