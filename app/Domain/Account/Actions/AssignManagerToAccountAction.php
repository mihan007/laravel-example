<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Models\User;

class AssignManagerToAccountAction
{
    public function execute(User $accountManager, Account $account)
    {
        AccountUser::query()->updateOrCreate(
            [
                'user_id' => $accountManager->id
            ],
            [
                'role' => User::ROLE_ACCOUNT_MANAGER_NAME,
                'user_id' => $accountManager->id,
                'account_id' => $account->id
            ],
        );
        $accountManager->detachRoles($accountManager->roles);
        $accountManager->attachRole(User::ROLE_ACCOUNT_MANAGER_ID);
        $accountManager->refresh();

        return $account;
    }
}
