<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Company\Models\Company;

class CalculateIncomeAction
{
    public function execute(Company $company, $startAt, $endAt)
    {
        return (int)$company
            ->paymentTransaction()
            ->income()
            ->timePeriod($startAt, $endAt)
            ->sum('amount');
    }
}
