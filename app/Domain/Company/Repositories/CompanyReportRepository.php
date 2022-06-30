<?php

namespace App\Domain\Company\Repositories;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Report\CompanyReportBuilder;
use App\Domain\User\Models\User;
use App\Support\Repositories\Report\ReportRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class CompanyListRepository.
 *
 * Display list of companies
 */
class CompanyReportRepository extends ReportRepository
{
    /** @var \App\Domain\User\Models\User */
    private $currentUser;

    /**
     * @var int|mixed|null
     */
    private $accountId;

    /**
     * CompanyListRepository constructor.
     * @param Request $request
     * @param $currentUser
     */
    public function __construct(Request $request, $currentUser)
    {
        $this->accountId = optional(Account::current())->id;
        $this->currentUser = $currentUser;
        parent::__construct($request);
    }

    /**
     * @param Request $request
     * @return CompanyReportBuilder
     */
    protected function createReportBuilder(Request $request): CompanyReportBuilder
    {
        $currentChannel = Channel::find($request->channel_id);
        $currentManager = $this->currentUser->isManager ? $this->currentUser : User::find($request->manager_id);

        return new CompanyReportBuilder(
            $request->get('start_at'),
            $request->get('end_at'),
            $this->currentUser,
            $currentChannel,
            null,
            $currentManager
        );
    }

    /**
     *
     */
    public function rebuildReport()
    {
        $this->reportBuilder->buildReport();
    }


    /**
     * Get paginate data.
     *
     * @return Collection
     */
    public function getAndPaginate(): Collection
    {
        $reportBuilder = $this->reportBuilder->getReport();
        $this->sortedReport($reportBuilder, 'companies');
        $reportBuilder = $this->addAccountFilter($reportBuilder);

        return $this->getPaginate($reportBuilder);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function addAccountFilter(Builder $builder): Builder
    {
        if (!$this->accountId) {
            return $builder;
        }

        $companies = Company::where('account_id', $this->accountId)->pluck('id')->toArray();

        return $builder->whereIn('companies.id', $companies);
    }

    /**
     * @return mixed
     */
    protected function getChannel()
    {
        return $this->currentUser->getCompaniesChannel();
    }
}
