<?php

namespace App\Domain\Company\Report;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use App\Domain\User\Models\User;
use App\Support\Builders\ReportBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CompanyReportBuilder.
 */
class CompanyReportBuilder extends ReportBuilder
{
    /** @var User */
    private $currentUser;

    /** @var Company */
    private $currentCompany;

    /** @var Channel */
    private $currentChannel;
    /**
     * @var User|null
     */
    private $currentManager;

    /**
     * CompanyReportBuilder constructor.
     * @param null $startAt
     * @param null $endAt
     * @param User|null $currentUser
     * @param Channel|null $currentChannel
     * @param Company|null $currentCompany
     * @param User|null $currentManager
     */
    public function __construct(
        $startAt = null,
        $endAt = null,
        User $currentUser = null,
        Channel $currentChannel = null,
        Company $currentCompany = null,
        User $currentManager = null
    ) {
        parent::__construct($startAt, $endAt);

        $this->currentUser = $currentUser;
        $this->currentCompany = $currentCompany;
        $this->currentChannel = $currentChannel;
        $this->currentManager = $currentManager;
    }

    /**
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return Company::query()
            ->leftJoin(
                'company_report',
                'company_report.company_id',
                '=',
                'companies.id'
            )
            ->leftJoin(
                'yandex_direct_company_configs',
                'yandex_direct_company_configs.company_id',
                '=',
                'companies.id'
            )
            ->addSelect('company_report.company_id')
            ->addSelect('companies.name');
    }

    /**
     * @return User|null
     */
    public function getCurrentManager(): ?User
    {
        return $this->currentManager;
    }

    public function buildReport(): void
    {
        $currentDate = (clone $this->startAt);
        $endAt = (clone $this->endAt);
        while ($currentDate <= $endAt) {
            $this->startAt = (clone $currentDate)->startOfDay();
            $this->endAt = (clone $currentDate)->endOfDay();
            $companies = $this->getCompanyBuilder()->get();
            $companies = $this->transformCompanies($companies);
            $this->storeCompaniesToReport($companies);
            $currentDate->addDay();
        }
    }

    /**
     * Create companies builder with necessary data.
     *
     * @return Builder
     */
    public function getCompanyBuilder(): Builder
    {
        $startAt = $this->startAt;
        $endAt = $this->endAt;

        $builder = Company::with(
            [
                'roistatConfig.mostRecentAnalytic',
                'roistatConfig.mostRecentGoogleAnalytic',
                'roistatBalanceConfig.latestTransaction',
                'yandexDirectConfig',
                'yandexLatestBalace',
                'roistatConfig.analytics' => function (\Illuminate\Database\Eloquent\Relations\HasMany $query) use (
                    $startAt,
                    $endAt
                ) {
                    $query->whereBetween(
                        'for_date',
                        [
                            $startAt,
                            $endAt,
                        ]
                    );
                },
            ]
        )
            ->select(
                [
                    '*',
                    DB::raw(
                        "(SELECT SUM(pt.amount) FROM payment_transactions pt
                                WHERE pt.company_id = companies.id
                                AND pt.status = 'paid'
                                AND pt.created_at BETWEEN '$startAt' AND '$endAt'
                            ) AS amount_add"
                    ),
                    DB::raw(
                        "(SELECT SUM(pt.amount) FROM payment_transactions pt
                                WHERE pt.company_id = companies.id
                                AND pt.payment_type = 'balance_operations'
                                AND pt.status = 'write-off'
                                AND pt.created_at BETWEEN '$startAt' AND '$endAt'
                            ) AS amount_remove"
                    ),
                    DB::raw(
                        "(SELECT SUM(ra.visitsCost) 
                            FROM roistat_analytics as ra
                            LEFT JOIN roistat_company_configs as rcc
                            ON rcc.id = ra.roistat_company_config_id
                            WHERE rcc.company_id = companies.id
                            AND ra.for_date BETWEEN '" . $startAt . "' AND '" . $endAt . "'
                            ) as costs"
                    ),
                    DB::raw(
                        "(SELECT COUNT(pl.id)
                            FROM pl_report_leads prl
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            WHERE pls.company_id = companies.id
                            AND prl.company_confirmed = 1
                            AND prl.created_at BETWEEN '" . $startAt . "' AND '" . $endAt . "'
                            AND pl.deleted_at IS NULL
                            ) as target_leads"
                    ),
                    DB::raw(
                        "(SELECT COUNT(pl.id)
                            FROM pl_report_leads prl
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            WHERE pls.company_id = companies.id
                            AND prl.admin_confirmed = 2
                            AND prl.created_at BETWEEN '" . $startAt . "' AND '" . $endAt . "'
                            AND pl.deleted_at IS NULL
                            ) as not_confirmed_leads"
                    ),
                    DB::raw(
                        "(SELECT COUNT(pl.id)
                            FROM pl_report_leads prl
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            WHERE pls.company_id = companies.id
                            AND prl.company_confirmed != 1
                            AND prl.created_at BETWEEN '" . $startAt . "' AND '" . $endAt . "'
                            AND pl.deleted_at IS NULL
                            ) as not_target_leads"
                    ),
                    DB::raw(
                        "(SELECT sum(pl.cost)
                            FROM pl_report_leads prl                           
                            JOIN proxy_leads pl ON prl.proxy_lead_id = pl.id
                            JOIN proxy_lead_settings pls ON pl.proxy_lead_setting_id = pls.id
                            WHERE pls.company_id = companies.id
                            AND prl.company_confirmed = 1
                            AND prl.created_at BETWEEN '" . $startAt . "' AND '" . $endAt . "'
                            AND pl.deleted_at IS NULL
                            ) as target_profit"
                    ),
                ]
            );

        if ($this->currentCompany) {
            $builder->where('id', $this->currentCompany->id);
        }
        if ($this->currentChannel) {
            $builder->where('companies.channel_id', $this->currentChannel->id);
        }
        if ($this->currentUser && !$this->currentUser->isAdmin) {
            $listOfCompaniesAvailableForUser = $this->currentUser->getCompanyForUser($this->currentUser->id)->pluck(
                'id'
            );
            if (count($listOfCompaniesAvailableForUser) > 0) {
                $builder->whereIn('id', $listOfCompaniesAvailableForUser);
            }
        }
        if ($this->currentManager) {
            $listOfManagerCompanies = $this->currentManager->getCompanyUser();
            $builder->whereIn('id', $listOfManagerCompanies);
        }

        return $builder;
    }

    /**
     * Trasform data in companies.
     *
     * @param Collection $companies
     * @return Collection
     */
    private function transformCompanies(Collection $companies): Collection
    {
        return $companies->each(
            function (Company $company) {
                $company->setAppends(
                    [
                        'google_status',
                        'roistat_status',
                        'yandex_status',
                    ]
                );

                $company->costs = $company->costs ?? 0;
                $company->target_leads = $company->target_leads ?? 0;
                $company->not_target_leads = $company->not_target_leads ?? 0;
                $company->cpl = $company->target_leads ? round($company->costs / $company->target_leads, 2) : 0;
                $allLeads = $company->target_leads + $company->not_target_leads;
                $company->target_percent = $allLeads ? round($company->target_leads / ($allLeads) * 100, 2) : 0;
                $company->amount_add = $company->amount_add ?? 0;
                $company->amount_remove = $company->amount_remove ?? 0;
                $company->amount = $company->amount_add - $company->amount_remove;
                $company->target_profit = ($company->prepayment && $company->target_profit) ? $company->target_profit : 0;
                $company->target_all = $allLeads;
            }
        );
    }

    /**
     * @param $companies
     */
    private function storeCompaniesToReport($companies): void
    {
        $companiesForRebuild = $this->getCurrentPeriodCompanyBuilder();
        $companiesForRebuild->delete();

        $companies->each(
            function (Company $company) {
                $record = [
                    'company_id' => $company->id,
                    'channel_id' => $company->channel_id,
                    'name' => $company->name,
                    'amount' => $company->amount,
                    'balance' => $company->balance,
                    'target_leads' => $company->target_leads,
                    'not_confirmed_leads' => $company->not_confirmed_leads,
                    'target_percent' => $company->target_percent,
                    'target_profit' => $company->target_profit,
                    'cpl' => $company->cpl,
                    'costs' => $company->costs,
                    'start_at' => $this->startAt,
                    'end_at' => $this->endAt,
                    'yandex_status' => $company->yandex_status,
                    'google_status' => $company->google_status,
                    'roistat_status' => $company->roistat_status,
                    'report_date' => $this->endAt,
                    'target_all' => $company->target_all,
                ];
                CompanyReport::create($record);
            }
        );
    }

    /**
     * @return Builder
     */
    public function getReportBuilder(): Builder
    {
        return Company::query()
            ->leftJoin(
                'company_report',
                'company_report.company_id',
                '=',
                'companies.id'
            )
            ->leftJoin(
                'yandex_direct_company_configs',
                'yandex_direct_company_configs.company_id',
                '=',
                'companies.id'
            )
            ->addSelect('company_report.company_id')
            ->addSelect('companies.name');
    }

    /**
     * @return Builder
     */
    public function getReport(): Builder
    {
        $reportBuilder = $this->getReportBuilder();
        $reportBuilder = $this->getRowsForReports($reportBuilder);
        $reportBuilder = $this->groupByReport($reportBuilder);

        return $this->getCurrentPeriodCompanyBuilder($reportBuilder);
    }

    /**
     * @param Builder $reportBuilder
     * @return Builder
     */
    public function groupByReport(Builder $reportBuilder): Builder
    {
        return $reportBuilder->groupBy('companies.id');
    }

    /**
     * @return mixed
     */
    protected function getChannel()
    {
        return $this->currentChannel;
    }

    /**
     * @param Builder|null $reportBuilder
     * @return Builder
     */
    private function getCurrentPeriodCompanyBuilder(Builder $reportBuilder = null): Builder
    {
        $reportBuilder = $reportBuilder ?? CompanyReport::query();

        $reportBuilder
            ->where('report_date', '>=', $this->startAt)
            ->where('report_date', '<=', $this->endAt);

        $companyChannel = $this->getChannel();
        $isCurrentUserAdmin = $this->currentUser ? $this->currentUser->isAdmin : true; // default is system

        if (Account::current()) {
            $accountCompaniesIds = Account::current()->companies->pluck('id')->toArray();
            $reportBuilder->whereIn('company_report.company_id', $accountCompaniesIds);
        }

        if (!$companyChannel) {
            if (!$isCurrentUserAdmin) {
                $listOfCompaniesAvailableForUser = $this->currentUser->getCompanyForUser($this->currentUser->id)->pluck(
                    'id'
                );
                return $reportBuilder->whereIn('company_report.company_id', $listOfCompaniesAvailableForUser);
            }
        }

        if ($isCurrentUserAdmin && is_numeric($companyChannel)) {
            $listOfCompaniesAvailableForUser = $this->currentUser->getCompanyForUser($companyChannel)->pluck('id');

            return $reportBuilder->whereIn('company_report.company_id', $listOfCompaniesAvailableForUser);
        }

        $channel = $companyChannel instanceof Channel ? $companyChannel : Channel::where(
            'slug',
            $companyChannel
        )->first();
        if ($channel) {
            $reportBuilder->where('companies.channel_id', $channel->id);
        }

        if ($this->currentManager) {
            $listOfManagerCompanies = $this->currentManager->companies->pluck('id')->toArray();
            $reportBuilder->whereIn('company_report.company_id', $listOfManagerCompanies);
        }

        if ($this->currentCompany) {
            $reportBuilder->where('company_report.company_id', $this->currentCompany->id);
        }

        return $reportBuilder;
    }
}
