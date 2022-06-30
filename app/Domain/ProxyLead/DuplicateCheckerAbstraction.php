<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 18.09.2018
 * Time: 17:08.
 */

namespace App\Domain\ProxyLead;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Illuminate\Support\Collection;

abstract class DuplicateCheckerAbstraction
{
    /** @var \App\Domain\ProxyLead\Models\ProxyLead|RoistatProxyLead */
    protected $lead;

    public function check()
    {
        $duplicates = $this->getDuplicates();

        if (0 === $duplicates->count()) {
            return false;
        }

        $targetDuplicates = $this->getTargetDuplicates($duplicates);

        if (0 === $targetDuplicates->count()) {
            return false;
        }

        $this->setDuplicate($targetDuplicates);

        return true;
    }

    abstract protected function getDuplicates(): Collection;

    abstract protected function getTargetDuplicates(Collection $duplicates): Collection;

    abstract protected function setDuplicate(Collection $duplicates): void;
}
