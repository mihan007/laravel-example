<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:11.
 */

namespace App\Support\Status;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\EmptyStatusConfiguration;
use App\Domain\ProxyLead\ProxyLeadStatusConfiguration;
use App\Domain\Roistat\RoistatStatusConfiguration;
use Carbon\Carbon;

class StatusConfigurationFactory
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
     * StatusConfigurationFactory constructor.
     * @param \App\Domain\Company\Models\Company $company
     */
    public function __construct(Company $company, Carbon $period)
    {
        $this->company = $company;
        $this->company->loadMissing('proxyLeadSettings', 'roistatConfig');
        $this->period = $period;
    }

    public function create()
    {
        if ($this->isProxyLeadSettingsConfigured()) {
            return new ProxyLeadStatusConfiguration($this->company, $this->period);
        }

        if ($this->company->roistatConfig !== null) {
            return new RoistatStatusConfiguration($this->company, $this->period);
        }

        return new EmptyStatusConfiguration($this->company, $this->period);
    }

    /**
     * @return bool
     */
    private function isProxyLeadSettingsConfigured(): bool
    {
        return $this->company->proxyLeadSettings !== null;
    }
}
