<?php

namespace App\ViewModels;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Spatie\ViewModels\ViewModel;

class UserFormViewModel extends ViewModel
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Account
     */
    private $account;

    public function __construct(Account $account, User $user)
    {
        $this->account = $account;
        $this->user = $user;
    }

    public function users()
    {
        return $this->account->users;
    }

    public function roles()
    {
        $availableRolesToAdd = [User::ROLE_ACCOUNT_MANAGER_ID];
        if ($this->user->is_super_admin) {
            $availableRolesToAdd[] = User::ROLE_ACCOUNT_ADMIN_ID;
        }

        return Role::whereIn('id', $availableRolesToAdd)->get();
    }

    public function accountId()
    {
        return $this->account->id;
    }
}
