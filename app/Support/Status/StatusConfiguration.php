<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:12.
 */

namespace App\Support\Status;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\ProxyLead\Models\ReconciliationBase;
use App\Support\Interfaces\StatusTypes;
use Carbon\Carbon;
use DB;

abstract class StatusConfiguration implements StatusTypes
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    protected $company;
    /**
     * @var Carbon
     */
    protected $period;
    /**
     * @var bool
     */
    protected $isEmptyConfiguration = false;

    /**
     * StatusConfiguration constructor.
     * @param \App\Domain\Company\Models\Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company, Carbon $period)
    {
        $this->company = $company;
        $this->period = $period;
    }

    /**
     * Get status.
     *
     * @return int
     */
    final public function get(): int
    {
        if ($this->isEmptyConfiguration) {
            return $this->getEmptyConfiguration();
        }

        return $this->identifyConfiguration();
    }

    /**
     * Get approve record from database.
     *
     * @return mixed
     */
    abstract protected function getApproveRecord();

    /**
     * Get last reconciliation.
     *
     * @return mixed
     */
    abstract protected function getLastReconciliation();

    /**
     * @return int
     */
    private function getTargetLeadsCount(): int
    {
        $targetLeadsCount = $this->company->proxyLeadGoalCounters()
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

        return $targetLeadsCount->isEmpty() ? 0 : $targetLeadsCount->get(0)['target_count'];
    }

    /**
     * @param $approve
     * @return bool
     */
    private function isApproved($approve): bool
    {
        return $approve !== null;
    }

    /**
     * @return \App\Domain\Finance\Models\FinanceReport|null
     */
    private function getFinanceReport()
    {
        return $this->company->financeReports()->where('for_date', $this->period->toDateString())->first();
    }

    /**
     * @param $financeReport
     * @return bool
     */
    private function isFinanceReportExists($financeReport): bool
    {
        return $financeReport !== null;
    }

    /**
     * @param $financeReport
     * @return float
     */
    private function getTotalPaymentAmount(FinanceReport $financeReport): float
    {
        return $financeReport->payments()->get()->sum('amount');
    }

    /**
     * @param $financeReport
     * @param $paymentsAmount
     * @return bool
     */
    private function isFullyPaid($financeReport, $paymentsAmount): bool
    {
        return $financeReport->to_pay <= $paymentsAmount;
    }

    /**
     * @param $lastReconciliation
     * @return bool
     */
    private function isEmptyReconciliation($lastReconciliation): bool
    {
        return null === $lastReconciliation;
    }

    /**
     * @param $lastReconciliation
     * @return bool
     */
    private function isUserReconciliation(ReconciliationBase $lastReconciliation): bool
    {
        return ReconciliationBase::USER_TYPE === $lastReconciliation->type;
    }

    /**
     * @param $lastReconciliation
     * @return bool
     */
    private function isAdminReconciliation(ReconciliationBase $lastReconciliation): bool
    {
        return ReconciliationBase::ADMIN_TYPE === $lastReconciliation->type;
    }

    /**
     * @return int
     */
    private function getEmptyConfiguration(): int
    {
        return static::NOT_CONFIGURED;
    }

    /**
     * @return int
     */
    private function identifyConfiguration(): int
    {
        $approve = $this->getApproveRecord();
        $targetLeadsCount = $this->getTargetLeadsCount();

        if (0 === $targetLeadsCount && $this->isApproved($approve)) {
            return static::NO_ORDERS;
        }

        $financeReport = $this->getFinanceReport();
        $paymentsAmount = ! $this->isFinanceReportExists($financeReport) ? 0 : $this->getTotalPaymentAmount($financeReport);

        if (
            $paymentsAmount > 0
            && $this->isFinanceReportExists($financeReport)
            && ! $this->isFullyPaid($financeReport, $paymentsAmount)
            && $this->isApproved($approve)
        ) {
            return static::PARTIALLY_PAID;
        }

        if (
            $paymentsAmount > 0
            && $this->isFinanceReportExists($financeReport)
            && $this->isFullyPaid($financeReport, $paymentsAmount)
            && $this->isApproved($approve)
        ) {
            return static::FULLY_PAID;
        }

        if ($this->isApproved($approve)) {
            return static::WAITING_FOR_PAYMENT;
        }

        $lastReconciliation = $this->getLastReconciliation();

        if ($this->isEmptyReconciliation($lastReconciliation)) {
            return static::COMPANY_RECONCILING;
        }

        if ($this->isUserReconciliation($lastReconciliation)) {
            return static::COMPANY_RECONCILING;
        }

        if ($this->isAdminReconciliation($lastReconciliation)) {
            return static::USER_RECONCILING;
        }

        return static::NOT_CONFIGURED;
    }
}
