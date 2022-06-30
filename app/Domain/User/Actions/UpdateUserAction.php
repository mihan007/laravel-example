<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DataTransferObjects\UserData;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function execute(User $user, UserData $userData)
    {
        $user->fill(
            [
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => Hash::make($userData->password),
                'activated' => $userData->activated,
            ]
        )->save();

        return $user->refresh();
    }
}
