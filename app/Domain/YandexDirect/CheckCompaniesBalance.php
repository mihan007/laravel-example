<?php

namespace App\Domain\YandexDirect;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\YandexDirect\Api\BaseApi;
use Illuminate\Support\Collection;

class CheckCompaniesBalance
{
    /**
     * @var BaseApi
     */
    private $yandexApi;

    public function __construct(BaseApi $yandexApi)
    {
        $this->yandexApi = $yandexApi;
    }

    /**
     * Check balance of all companies and store it into database.
     *
     * @return bool
     */
    public function check(): bool
    {
        foreach ($this->getActiveCompanies() as $company) {
            $balance = $this->getCompanyBalance($company);

            if ($this->doesNotHaveBalance($balance)) {
                continue;
            }

            $this->addBalance($company, $balance);
        }

        return true;
    }

    private function getActiveCompanies(): Collection
    {
        $companies = Company::has('yandexDirectConfig')->with('yandexDirectConfig')->get();

        return $companies->filter(
            function (Company $company) {
                return ! empty($company->yandexDirectConfig->yandex_auth_key);
            }
        );
    }

    private function getCompanyBalance(Company $company): ?float
    {
        return (new CompanyBalanceGetter($company, $this->yandexApi))->get();
    }

    private function doesNotHaveBalance($balance): bool
    {
        return false === $balance;
    }

    private function addBalance(Company $company, float $balance)
    {
        $company->yandexBalances()->create(['amount' => $balance]);
        if ($balance > $company->yandexDirectConfig->limit_amount) {
            $company->clearEmailNotificationLastSend(EmailNotification::YANDEX_DIRECT_TYPE);
        }
    }
}
