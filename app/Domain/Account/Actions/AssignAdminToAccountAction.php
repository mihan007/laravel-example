<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Actions\RevokeUserAccessToAllCompaniesAction;
use App\Domain\User\Models\User;

class AssignAdminToAccountAction
{
    /**
     * @var RevokeUserAccessToAllCompaniesAction
     */
    private RevokeUserAccessToAllCompaniesAction $revokeUserAccessToAllCompanies;

    public function __construct(RevokeUserAccessToAllCompaniesAction $revokeUserAccessToAccountCompanies)
    {
        $this->revokeUserAccessToAllCompanies = $revokeUserAccessToAccountCompanies;
    }

    public function execute(User $user, Account $account)
    {
        if ($user->account && ($user->account->id != $account->id)) {
            $this->revokeUserAccessToAllCompanies->execute($user);
        }

        AccountUser::updateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'account_id' => $account->id,
                'role' => User::ROLE_ACCOUNT_ADMIN_NAME
            ]
        );
        $user->detachRoles($user->roles);

        $user->attachRole(User::ROLE_ACCOUNT_ADMIN_ID);
        $user->refresh();

        return $account;
    }
}
