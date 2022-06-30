<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.08.2018
 * Time: 8:35.
 */

namespace App\Domain\Company;

use App\Domain\Finance\Models\FinanceReport;
use App\Support\Status\Status;
use Carbon\Carbon;

trait Financing
{
    /**
     * Get company finance status.
     *
     * @param Carbon $period
     * @return int
     */
    public function getFinanceStatus(Carbon $period)
    {
        return (new Status($this, $period))->get();
    }

    /**
     * It has many finance reports.
     *
     * @return mixed
     */
    public function financeReports()
    {
        return $this->hasMany(FinanceReport::class);
    }
}
