<?php

namespace App\Domain\Company\Actions;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\BalanceNotifier;

class UpdateCompanyBalanceAction
{
    public function __construct()
    {
    }

    public function execute(Company $company, $amount)
    {
        $val = ($amount >= 0 ? '+' : '') . $amount;
        $sql = 'UPDATE `companies` SET `balance`=`balance`' . $val . " where id={$company->id}";
        \DB::update(\DB::raw($sql));
        $company->refresh();

        if ($company->date_stop_leads && $company->balance >= $company->amount_limit) {
            $company->date_stop_leads = null;
        } else {
            if (! $company->date_stop_leads && $company->balance < $company->amount_limit) {
                $company->date_stop_leads = $company->getDateWhenLastLeadCreated();
            }
        }
        $company->save();

        new BalanceNotifier($company);
    }
}
