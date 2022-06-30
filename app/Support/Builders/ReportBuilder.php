<?php

namespace App\Support\Builders;

use App\Domain\Company\Models\CompanyReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ReportBuilder.
 */
abstract class ReportBuilder implements ReportBuilderInterface
{
    /** @var Carbon */
    protected $startAt;

    /** @var Carbon */
    protected $endAt;

    /**
     * CompanyReportBuilder constructor.
     * @param null $startAt
     * @param null $endAt
     */
    public function __construct(
        $startAt = null,
        $endAt = null
    ) {
        $this->startAt = !empty($startAt) ? Carbon::parse($startAt)->startOfDay() : now()->startOfMonth()->startOfDay();
        $this->endAt = !empty($endAt) ? Carbon::parse($endAt)->endOfDay() : now()->endOfDay();
    }

    /**
     * @return Builder
     */
    abstract public function getBuilder(): Builder;

    /**
     * @param Builder $reportBuilder
     * @return Builder
     */
    abstract public function groupByReport(Builder $reportBuilder): Builder;

    /**
     * @return Builder
     */
    abstract public function getReport(): Builder;

    /**
     * @param $reportBuilder
     * @return Builder
     */
    protected function getRowsForReports($reportBuilder): Builder
    {
        return $reportBuilder
            ->selectRaw('ROUND(companies.balance) as balance')
            ->selectRaw('SUM(company_report.amount) AS `amount`')
            ->selectRaw('SUM(company_report.target_all) AS `target_all`')
            ->selectRaw('SUM(company_report.target_leads) AS `target_leads`')
            ->selectRaw('SUM(company_report.not_confirmed_leads) AS `not_confirmed_leads`')
            ->selectRaw(
                'ROUND((SUM(company_report.target_leads) / SUM(company_report.target_all) * 100)) AS `target_percent`'
            )
            ->selectRaw('SUM(company_report.target_profit) AS `target_profit`')
            ->selectRaw('ROUND(SUM(company_report.costs) / SUM(company_report.target_leads)) AS `cpl`')
            ->selectRaw('SUM(company_report.costs) AS `costs`')
            ->addSelect('company_report.yandex_status')
            ->addSelect('company_report.google_status')
            ->addSelect('company_report.roistat_status')
            ->addSelect('company_report.report_date')
            ->selectRaw(
                'IF(yandex_direct_company_configs.amount is null , "-", ROUND(yandex_direct_company_configs.amount)) as yandex_balance'
            )
            ->selectRaw('"-" as google_balance')
            ->selectRaw(
                'IF(companies.profit_calculate = 1 , SUM(company_report.target_profit)-SUM(company_report.costs), SUM(company_report.target_profit)) as profit'
            );
    }

    /**
     * @param Builder $reportBuilder
     * @return Builder
     */
    public function getSummaryReportBuilder(Builder $reportBuilder): Builder
    {
        $summaryQuery = $reportBuilder->toBase();
        $summaryQuery->groups = null;

        $summaryReportBuilder = CompanyReport::setQuery($summaryQuery);
        $summaryReportBuilder
            ->select([])
            ->selectRaw('SUM(company_report.target_all) AS `target_all`')
            ->selectRaw('SUM(company_report.target_leads) AS `target_leads`')
            ->selectRaw('ROUND(SUM(target_leads)/SUM(target_all)*100)  AS `target_percent`')
            ->selectRaw('ROUND(SUM(company_report.costs)/SUM(target_leads)) AS `cpl`')
            ->selectRaw('SUM(company_report.not_confirmed_leads) AS `not_confirmed_leads`')
            ->selectRaw('SUM(company_report.target_profit) AS `target_profit`')
            ->selectRaw('SUM(company_report.costs) AS `costs`')
            ->selectRaw(
                'SUM(CASE WHEN companies.profit_calculate = 1 THEN (company_report.target_profit - company_report.costs) ELSE company_report.target_profit END) AS profit'
            )
            ->selectRaw('ROUND(SUM(companies.balance) * COUNT(DISTINCT companies.id) / COUNT(*)) AS `balance`')
            ->selectRaw(
                'ROUND(SUM(yandex_direct_company_configs.amount) * COUNT(DISTINCT companies.id) / COUNT(*)) AS `yandex_balance`'
            );

        return $summaryReportBuilder;
    }

    /**
     * @return Carbon
     */
    public function getStartAt(): Carbon
    {
        return $this->startAt;
    }

    /**
     * @return Carbon
     */
    public function getEndAt(): Carbon
    {
        return $this->endAt;
    }

    /**
     * @param Builder $reportBuilder
     * @return Builder
     */
    protected function getPeriodCompanyBuilder(Builder $reportBuilder): Builder
    {
        $reportBuilder
            ->where('report_date', '>=', $this->startAt)
            ->where('report_date', '<=', $this->endAt);

        return $reportBuilder;
    }
}
