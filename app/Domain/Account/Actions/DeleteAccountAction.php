<?php


namespace App\Domain\Account\Actions;


use App\Domain\Account\Models\Account;

class DeleteAccountAction
{
    public function execute(Account $account)
    {
        foreach ($account->companies as $company) {
            $company->proxyLeads()->delete();
        }
        $account->companies()->delete();
        $account->aboutCompany()->delete();
        $account->accountSetting()->delete();
        $account->channels()->delete();
        $account->delete();
    }
}
