<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 18.09.2018
 * Time: 21:10.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Notification\Mail\ApproveNotification;
use App\Domain\Notification\Models\EmailNotification;
use App\Support\Interfaces\StatusTypes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class ApproveNotificationSender
{
    /**
     * @var Carbon
     */
    private $period;

    /**
     * ApproveNotificationSender constructor.
     * @param Carbon $period
     */
    public function __construct(Carbon $period)
    {
        $this->period = $period;
    }

    public function send()
    {
        $companies = $this->getCompanies();

        $companies->each(function (Company $company, $key) {
            $this->sendNotification($company);
        });

        return true;
    }

    private function getCompanies(): Collection
    {
        $reports = FinanceReport::with(['company.emailNotifications' => function (HasMany $query) {
            $query->where('type', EmailNotification::REPORT_TYPE);
        }])
            ->where('for_date', $this->period->toDateString())
            ->whereNotIn('status', [StatusTypes::NO_ORDERS])
            ->get();

        return $reports->pluck('company');
    }

    private function sendNotification(Company $company)
    {
        return Mail::to($company->emailNotifications->pluck('email')->toArray())
            ->send(new ApproveNotification($company, $this->period));
    }
}
