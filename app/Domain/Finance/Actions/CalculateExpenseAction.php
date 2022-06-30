<?php

namespace App\Domain\Finance\Actions;

use App\Domain\Company\Models\Company;

class CalculateExpenseAction
{
    public function execute(Company $company, $startAt, $endAt)
    {
        return (int)$company
            ->paymentTransaction()
            ->expense()
            ->timePeriod($startAt, $endAt)
            ->sum('amount');
    }
}
