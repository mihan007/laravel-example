<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 15.05.2017
 * Time: 9:25.
 */

namespace App\Domain\YandexDirect;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\YandexDirectNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MailNotifications
{
    public function __construct()
    {
    }

    public function check()
    {
        /** @var \App\Domain\Company\Models\Company[] $companies */
        $companies = Company::with(['yandexDirectConfig', 'yandexDirectEmailNotifications'])->get();

        foreach ($companies as $company) {
            if (empty($company->yandexDirectEmailNotifications)) {
                continue;
            }

            $yandexLatestBalance = $company->yandexLatestBalace()->first();

            if (empty($yandexLatestBalance) || $yandexLatestBalance->created_at->toDateString() !== Carbon::now(
                )->toDateString()) {
                continue;
            }

            if (empty($company->yandexDirectConfig) || empty($company->yandexDirectConfig->yandex_auth_key)) {
                continue;
            }

            if (! ($yandexLatestBalance->amount <= $company->yandexDirectConfig->limit_amount)) {
                continue;
            }

            // if current balance lower limit - send notification
            foreach ($company->getYandexNotificationEmailsForSend as $emailNotification) {
                Mail::send(new YandexDirectNotification($company, $yandexLatestBalance, $emailNotification->email));
            }
        }

        return true;
    }
}
