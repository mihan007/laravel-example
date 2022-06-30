<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.06.2018
 * Time: 15:44.
 */

namespace App\Domain\ProxyLead;

use App\Domain\Company\Models\Company;

class TargetCounterFactory
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;

    /**
     * TargetCounterFactory constructor.
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;

        $this->company->loadMissing('proxyLeadSettings');
    }

    public function get()
    {
        return new MainTargetCounter();
    }
}
