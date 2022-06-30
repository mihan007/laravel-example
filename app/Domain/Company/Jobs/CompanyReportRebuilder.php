<?php

namespace App\Domain\Company\Jobs;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Observers\CompanyObserver;
use App\Domain\Company\Report\CompanyReportBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class CompanyReportRebuilder.
 */
class CompanyReportRebuilder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var string */
    private $startAt;

    /** @var string */
    private $endAt;

    /** @var \App\Domain\Company\Models\Company */
    private $company;

    /**
     * CompanyReportRebuilder constructor.
     *
     * @param $startAt
     * @param null $endAt
     * @param \App\Domain\Company\Models\Company|null $company
     */
    public function __construct($startAt, $endAt = null, Company $company = null)
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt ?? $startAt;
        $this->company = $company;
    }

    public function handle()
    {
        (new CompanyReportBuilder($this->startAt, $this->endAt, null, null, $this->company))
            ->buildReport();
        (new CompanyObserver())->updateCompanyReport($this->company);
    }
}
