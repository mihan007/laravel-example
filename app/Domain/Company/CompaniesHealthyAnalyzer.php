<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 03.10.2017
 * Time: 8:57.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use App\Domain\Notification\Mail\MainNotification;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class CompaniesHealthyAnalyzer
{
    public function analyze()
    {
        $companies = Company::get();

        foreach ($companies as $company) {
            if (!$company->hasRoistatConfig()) {
                continue;
            }

            $this->checkHealth($company);
        }

        return true;
    }

    /**
     * Check company health and sending information to recipients.
     *
     * @param Company $company
     * @return bool
     */
    private function checkHealth($company)
    {
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig */
        $roistatConfig = $company->roistatConfig()->first();
        $message = '';

        /** @var \App\Domain\Company\Models\CompanyReport $yesterdayReport */
        $yesterdayReport = CompanyReport::where('company_id', $company->id)
            ->where('report_date', Carbon::yesterday()->format('Y-m-d'))
            ->first();

        if (!$yesterdayReport) {
            $message = 'У компании нет отчета за вчерашний день';
        } else {
            $maxLeadPriceLength = strlen(trim($roistatConfig->max_lead_price));
            if (($maxLeadPriceLength>0) && ($yesterdayReport->cpl > $roistatConfig->max_lead_price)) {
                $message .= "Превышена цена лида: Допустимая - {$roistatConfig->max_lead_price}, Текущая - {$yesterdayReport->cpl}<br>";
            }

            $maxCostLength = strlen(trim($roistatConfig->max_costs));
            if (($maxCostLength > 0) && ((int)$yesterdayReport->target_leads === 0) && ($yesterdayReport->costs > $roistatConfig->max_costs)) {
                $message .= "Превышено число расходов: Допустимая - {$roistatConfig->max_costs}, Текущая - {$yesterdayReport->costs}<br>";
            }
        }

        $recipients = $company->mainNotifications()->get();
        if (empty($message)) {
            return true;
        }

        if (empty($recipients)) {
            Mail::send(new MainNotification($company, $message, null));
            return true;
        }

        return $this->sendMessageToRecipients($company, $recipients->pluck('email')->all(), $message);
    }

    /**
     * @param $company
     * @param $recipients
     * @param $message
     * @return bool
     */
    private function sendMessageToRecipients($company, $recipients, $message)
    {
        foreach ($recipients as $recipient) {
            Mail::send(new MainNotification($company, $message, $recipient));
        }

        return true;
    }
}
