<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DataTransferObjects\UserData;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(UserData $userData)
    {
        $user = User::create(
            [
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => Hash::make($userData->password),
                'activated' => $userData->activated,
            ]
        );

        return $user;
    }
}
