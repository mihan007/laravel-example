<?php

namespace App\Domain\User\Actions;

use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Models\User;

class RevokeUserAccessToAllCompaniesAction
{
    public function execute(User $user)
    {
        CompanyRoleUser::where('user_id', $user->id)->delete();
    }
}
