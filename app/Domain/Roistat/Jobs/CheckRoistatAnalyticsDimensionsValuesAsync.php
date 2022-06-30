<?php

namespace App\Domain\Roistat\Jobs;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\CheckDimensionsValues;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckRoistatAnalyticsDimensionsValuesAsync implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Company|\App\Domain\Company\Models\Company[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    private $company;
    private $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $company = Company::findOrFail($this->companyId);

        return (new CheckDimensionsValues())->check($company);
    }
}
