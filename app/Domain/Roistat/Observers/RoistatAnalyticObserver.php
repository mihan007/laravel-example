<?php

namespace App\Domain\Roistat\Observers;

use App\Domain\Company\Jobs\CompanyReportRebuilder;
use App\Domain\Roistat\Models\RoistatAnalytic;

/**
 * Class RoistatAnalyticObserver.
 */
class RoistatAnalyticObserver
{
    /**
     * @param \App\Domain\Roistat\Models\RoistatAnalytic $entity
     */
    public function created(RoistatAnalytic $entity)
    {
        CompanyReportRebuilder::dispatch($entity->for_date, $entity->for_date, $entity->roistatCompanyConfig->company);
    }

    /**
     * @param \App\Domain\Roistat\Models\RoistatAnalytic $entity
     */
    public function deleted(RoistatAnalytic $entity)
    {
        CompanyReportRebuilder::dispatch($entity->for_date, $entity->for_date, $entity->roistatCompanyConfig->company);
    }

    /**
     * @param \App\Domain\Roistat\Models\RoistatAnalytic $entity
     */
    public function updated(RoistatAnalytic $entity)
    {
        CompanyReportRebuilder::dispatch($entity->for_date, $entity->for_date, $entity->roistatCompanyConfig->company);
    }
}
