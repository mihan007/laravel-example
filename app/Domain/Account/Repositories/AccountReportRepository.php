<?php

namespace App\Domain\Account\Repositories;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Report\AccountReportBuilder;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Report\CompanyReportBuilder;
use App\Domain\User\Models\User;
use App\Support\Builders\ReportBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Support\Repositories\Report\ReportRepository;

/**
 * Class AccountReportRepository.
 *
 * Display list of companies
 */
class AccountReportRepository extends ReportRepository
{
    const ACCOUNTS_TABLE = 'accounts';

    public function getAndPaginate(): Collection
    {
        $reportBuilder = $this->reportBuilder->getReport();
        $reportBuilder = $this->sortedReport($reportBuilder, self::ACCOUNTS_TABLE);

        return $this->getPaginate($reportBuilder);
    }

    protected function createReportBuilder(Request $request): AccountReportBuilder
    {
        return new AccountReportBuilder(
            $request->get('start_at'),
            $request->get('end_at')
        );
    }
}
