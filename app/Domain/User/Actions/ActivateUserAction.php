<?php

namespace App\Domain\User\Actions;

use App\Domain\User\Models\User;

class ActivateUserAction
{
    public function execute(User $user)
    {
        $user->activated = true;
        $user->save();
        $user->refresh();

        return $user;
    }
}
