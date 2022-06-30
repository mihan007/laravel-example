<?php

namespace App\Support\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ReportBuilderInterface.
 */
interface ReportBuilderInterface
{
    public function __construct($startAt = null, $endAt = null);
    public function getBuilder(): Builder;

    public function groupByReport(Builder $reportBuilder): Builder;

    public function getReport(): Builder;

}
