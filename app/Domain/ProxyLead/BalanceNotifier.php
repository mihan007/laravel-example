<?php

namespace App\Domain\ProxyLead;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\AdminBalanceLimitCheck;
use App\Domain\Notification\Mail\AdminSendBalanceLimitReplenishment;
use App\Domain\Notification\Mail\CustomerBalanceNotification;
use Mail;

class BalanceNotifier
{
    const LIMIT_BALANCE_OFF = 1000;
    const NOTIFY_MANAGER_LIMIT = 1500;

    public function __construct(Company $company)
    {
        if ($company->balance < $company->amount_limit || $company->balance >= self::NOTIFY_MANAGER_LIMIT) {
            $plEmails = $company->mainNotifications()->get();
            if ($plEmails->count() === 0) {
                $plEmails->add($company->account->warningEmail);
            }

            if ($company->balance + self::LIMIT_BALANCE_OFF <= $company->amount_limit && ! $company->balance_stop) {
                foreach ($plEmails->pluck('email') as $email) {
                    Mail::send(new AdminBalanceLimitCheck($company, $email));
                }
                $company->balance_stop = 1;
                $company->save();
            } elseif ($company->balance >= 0 && $company->balance_stop) {
                foreach ($plEmails->pluck('email') as $email) {
                    Mail::send(new AdminSendBalanceLimitReplenishment($company, $email));
                }

                $company->balance_stop = 0;
                $company->save();
            }
        }

        if ($company->prepayment && ! $company->free_period && $company->balance <= $company->balance_limit && ! $company->balance_send_notification) {
            $notificationSettings = $company->customerBalanceLimitNotifications()->get();

            if ($notificationSettings->count() === 0) {
                Mail::send(new CustomerBalanceNotification($company));
            } else {
                /* @var \App\Domain\Notification\Models\EmailNotificationSetting $setting */
                foreach ($notificationSettings as $email) {
                    Mail::send(new CustomerBalanceNotification($company, $email));
                }
            }

            $company->balance_send_notification = 1;
            $company->save();
        } elseif ($company->prepayment && ! $company->free_period && $company->balance > $company->balance_limit && $company->balance_send_notification) {
            $company->balance_send_notification = 0;
            $company->save();
        }
    }
}
