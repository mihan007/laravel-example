<?php

namespace App\Support\Repositories\Report;

use App\Domain\Company\Models\CompanyReport;
use App\Support\Builders\ReportBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Support\Requests\TablesListRequest;

/**
 * Class ReportRepository.
 *
 * Display reports
 */
abstract class ReportRepository
{
    /** @var Carbon */
    protected $startAt;

    /** @var Carbon */
    protected $endAt;

    /**
     * @var ReportBuilder
     */
    protected ReportBuilder $reportBuilder;

    /**
     * @var TablesListRequest
     */
    protected TablesListRequest $filterFromRequest;

    /**
     * ReportRepository constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->filterFromRequest = new TablesListRequest($request);
        $this->reportBuilder = $this->createReportBuilder($request);
        $this->startAt = $this->reportBuilder->getStartAt();
        $this->endAt = $this->reportBuilder->getEndAt();
    }

    abstract protected function createReportBuilder(Request $request);

    /**
     * Get paginate data.
     *
     * @return Collection
     */
    abstract public function getAndPaginate();

    /**
     * @param Builder $builder
     * @param $table
     * @return Builder
     */
    protected function sortedReport(Builder $builder, $table): Builder
    {
        $builder = $this->addSort($builder, $table);

        return $this->addSearchByName($builder, $table);
    }

    /**
     * @param Builder $builder
     * @return Collection
     */
    protected function getPaginate(Builder $builder): Collection
    {
        $page = ($this->filterFromRequest->getStartPage() / $this->filterFromRequest->getPaginationAmount()) + 1;
        $paginator = $builder->paginate(10, ['*'], 'page', $page);

        if ($summary = $this->reportBuilder->getSummaryReportBuilder($builder)->first()) {
            $this->transformSummary($summary);
        }

        $paginatorArray = $paginator->toArray();
        $paginatorArray['start_at'] = $this->startAt->toDateString();
        $paginatorArray['end_at'] = $this->endAt->toDateString();
        $paginatorArray['summary'] = $summary;
        $paginatorArray['recordsTotal'] = $paginator->total();
        $paginatorArray['recordsFiltered'] = $paginator->total();

        return collect($paginatorArray);
    }

    /**
     * Add search query by name.
     *
     * @param Builder $builder
     * @return Builder
     */
    protected function addSearchByName(Builder $builder, $table): Builder
    {
        $filter = $this->filterFromRequest->getSearchQuery();

        if ($filter) {
            return $builder->whereRaw(
                $table . '.name LIKE ? ',
                ['%' . trim($filter) . '%'],
            );
        }

        return $builder;
    }

    /**
     * Builder sort
     *
     * @param Builder $builder
     * @return Builder
     */
    protected function addSort(Builder $builder, $table): Builder
    {
        $sortName = $this->filterFromRequest->getSortName();
        $sortType = $this->filterFromRequest->getSortType();

        if ($sortName == 'name') {
            $sortName = $table . '.name';
        }

        if (!$this->isSortRequired($sortType, $sortName)) {
            return $builder->orderBy($table . '.id', 'DESC');
        }

        return $builder->orderBy($sortName, $sortType)->orderBy($table . '.id', 'DESC');
    }

    /**
     * @param string $sortType
     * @param string $sortName
     * @return bool
     */
    protected function isSortRequired(string $sortType, string $sortName): bool
    {
        return !empty($sortType)
            && !empty($sortName)
            && !in_array($sortName, ['yandex_status', 'google_status', 'roistat_status']);
    }

    /**
     * @param $companyReportSummary
     * @return mixed
     */
    protected function transformSummary(CompanyReport $companyReportSummary): CompanyReport
    {
        $companyReportSummary->yandex_balance = round($companyReportSummary->yandex_balance) ?? 0;
        $companyReportSummary->target_percent = $companyReportSummary->target_percent ?? 0;
        $companyReportSummary->cpl = $companyReportSummary->cpl ?? 0;
        $companyReportSummary->balance = $companyReportSummary->balance ?? 0;
        $companyReportSummary->costs = $companyReportSummary->costs ?? 0;
        $companyReportSummary->not_confirmed_leads = $companyReportSummary->not_confirmed_leads ?? 0;
        $companyReportSummary->profit = $companyReportSummary->profit ?? 0;
        $companyReportSummary->balance = $companyReportSummary->balance ?? 0;
        $companyReportSummary->target_all = $companyReportSummary->target_all ?? 0;
        $companyReportSummary->target_leads = $companyReportSummary->target_leads ?? 0;
        $companyReportSummary->target_profit = $companyReportSummary->target_profit ?? 0;

        return $companyReportSummary;
    }
}
