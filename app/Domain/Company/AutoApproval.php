<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 19.09.2018
 * Time: 17:58.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Support\Interfaces\Approvable;
use App\Support\Interfaces\ReportLeads;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AutoApproval
{
    /**
     * @var Carbon
     */
    private $period;

    /**
     * AutoApproval constructor.
     * @param Carbon $period
     */
    public function __construct(Carbon $period)
    {
        $this->period = $period;
    }

    public function check()
    {
        $companies = $this->getCompanies();

        $companiesWithoutLeads = $this->filterCompanies($companies);

        return $this->approveCompanies($companiesWithoutLeads);
    }

    private function getCompanies(): Collection
    {
        return Company::with([
                'proxyLeadSettings.leads' => function (\Illuminate\Database\Eloquent\Relations\Relation $query) {
                    $query->whereBetween(
                        'created_at',
                        [(clone $this->period)->toDateString(), (clone $this->period)->endOfMonth()->toDateString()]
                    );
                },
                'roistatConfig.leads' => function (\Illuminate\Database\Eloquent\Relations\Relation $query) {
                    $query->whereBetween(
                        'creation_date',
                        [(clone $this->period)->toDateString(), (clone $this->period)->endOfMonth()->toDateString()]
                    );
                },
            ])
            ->get();
    }

    private function filterCompanies(Collection $companies): Collection
    {
        return $companies->filter(function (Company $company, $key) {
            /** @var ReportLeads $config */
            $config = $company->getProxyLeadConfig();

            if (null === $config) {
                return false;
            }

            return $config->leads->count() === 0;
        });
    }

    private function approveCompanies(Collection $companiesWithoutLeads)
    {
        $companiesWithoutLeads->each(function (Company $company, $key) {
            /** @var \App\Support\Interfaces\Approvable $config */
            $config = $company->getProxyLeadConfig();

            $config->approves()->firstOrCreate(['for_date' => $this->period->toDateString()]);
        });
    }
}
