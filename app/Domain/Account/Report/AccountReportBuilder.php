<?php

namespace App\Domain\Account\Report;

use App\Domain\Account\Models\Account;
use App\Support\Builders\ReportBuilder;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AccountReportBuilder.
 */
class AccountReportBuilder extends ReportBuilder
{
    public function getBuilder(): Builder
    {
        return Account::query()
            ->leftJoin('companies', 'accounts.id', '=', 'companies.account_id')
            ->leftJoin(
                'company_report',
                'companies.id',
                '=',
                'company_report.company_id'
            )
            ->leftJoin(
                'yandex_direct_company_configs',
                'companies.id',
                '=',
                'yandex_direct_company_configs.company_id'
            )
            ->addSelect('accounts.id')
            ->addSelect('accounts.name');
    }

    public function groupByReport(Builder $reportBuilder): Builder
    {
        return $reportBuilder->groupBy('accounts.id');
    }

    public function getReport(): Builder
    {
        $reportBuilder = $this->getBuilder();
        $reportBuilder = $this->getRowsForReports($reportBuilder);
        $reportBuilder = $this->groupByReport($reportBuilder);

        return $this->getPeriodCompanyBuilder($reportBuilder);
    }
}
