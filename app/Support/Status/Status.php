<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.08.2018
 * Time: 8:52.
 */

namespace App\Support\Status;

use App\Domain\Company\Models\Company;
use App\Support\Interfaces\StatusTypes;
use Carbon\Carbon;

class Status implements StatusTypes
{
    /** @var StatusConfiguration */
    protected $configuration;

    /**
     * Status constructor.
     * @param \App\Domain\Company\Models\Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company, Carbon $period)
    {
        // we use it in many places and i don't want to change constructor to set new way of using this class
        // and it is not good idea to create it here but i didn't find best way to make it right
        $this->configuration = (new StatusConfigurationFactory($company, $period))->create();
    }

    /**
     * @return int
     */
    public function get(): int
    {
        return $this->configuration->get();
    }
}
