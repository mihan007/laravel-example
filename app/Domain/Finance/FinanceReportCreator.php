<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.08.2018
 * Time: 14:17.
 */

namespace App\Domain\Finance;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceReportCreator
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;
    /**
     * @var Carbon
     */
    private $period;

    /**
     * FinanceReportCreator constructor.
     * @param Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company, Carbon $period)
    {
        $this->company = $company;
        $this->period = clone $period;
    }

    public function create()
    {
        $leadCount = $this->getLeadCount();

        $financeReport = $this->getFinanceReport();

        $financeReport->fill([
                'status' => $this->company->getFinanceStatus($this->period),
                'lead_count' => $leadCount,
                'lead_cost' => $this->company->lead_cost,
                'to_pay' => $this->company->lead_cost * $leadCount,
            ])
            ->save();

        $paidAmount = $this->getPaidAmount($financeReport);

        $financeReport->paid = $paidAmount;
        $financeReport->save();
    }

    /**
     * Get amount of target leads for certain period.
     *
     * @return int
     */
    private function getLeadCount(): int
    {
        $leadCounter = $this->company->proxyLeadGoalCounters()
            ->whereBetween(
                'for_date',
                [
                    (clone $this->period)->startOfMonth()->toDateString(),
                    (clone $this->period)->endOfMonth()->toDateString(),
                ]
            )
            ->select(DB::raw('sum(target) as target_count'))
            ->groupBy('company_id')
            ->get();

        return $leadCounter->isEmpty() ? 0 : $leadCounter->get(0)['target_count'];
    }

    /**
     * @return \App\Domain\Finance\Models\FinanceReport
     */
    private function getFinanceReport(): FinanceReport
    {
        return $this->company->financeReports()->firstOrNew(['for_date' => $this->period->toDateString()]);
    }

    /**
     * Get amount of paids.
     *
     * @param $financeReport
     * @return int
     */
    private function getPaidAmount(FinanceReport $financeReport): int
    {
        $paidAmount = $financeReport->payments()
            ->groupBy('finance_report_id')
            ->select(DB::raw('sum(amount) as amount'))
            ->get();

        return $paidAmount->isEmpty() ? 0 : $paidAmount->get(0)['amount'];
    }
}
