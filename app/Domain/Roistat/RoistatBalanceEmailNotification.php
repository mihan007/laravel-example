<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 18.09.2017
 * Time: 16:17.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\RoistatBalanceNotification;
use App\Domain\Roistat\Models\RcBalanceTransaction;
use Illuminate\Support\Facades\Mail;

class RoistatBalanceEmailNotification
{
    public function __construct()
    {
    }

    public function check()
    {
        $companies = Company::with(
            [
                'roistatBalanceConfig',
                'roistatBalanceConfig.latestTransaction',
                'roistatConfig',
                'roistatBalanceNotifications',
            ]
        )->get();

        foreach ($companies as $company) {
            if (
                empty($company->roistatBalanceConfig) ||
                empty($company->roistatConfig) ||
                empty($company->roistatConfig->roistat_project_id) ||
                empty($company->roistatConfig->api_key)
            ) {
                continue;
            }

            if (empty($company->roistatBalanceConfig->limit_amount)) {
                continue;
            }
            if ($company->roistatBalanceConfig->latestTransaction->isEmpty()) {
                continue;
            }

            /** @var \App\Domain\Roistat\Models\RcBalanceTransaction $lastTransaction */
            $lastTransaction = $company->roistatBalanceConfig->latestTransaction->first();

            if (! ($lastTransaction->balance <= $company->roistatBalanceConfig->limit_amount)) {
                continue;
            }
            if (empty($company->roistatBalanceNotifications)) {
                Mail::send(
                    new RoistatBalanceNotification(
                        $company,
                        $company->roistatBalanceConfig->latestTransaction->first()->balance,
                        false
                    )
                );
                continue;
            }

            // if current balance lower limit - send notification
            foreach ($company->getRoistatNotificationEmailsForSend as $emailNotification) {
                Mail::send(
                    new RoistatBalanceNotification(
                        $company,
                        $company->roistatBalanceConfig->latestTransaction->first()->balance,
                        $emailNotification->email
                    )
                );
            }
        }

        return true;
    }
}
