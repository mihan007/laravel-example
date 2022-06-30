<?php

use Faker\Factory;
use Faker\Generator;
use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;

if (! function_exists('faker')) {
    function faker(): Generator
    {
        return Factory::create();
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?User
    {
        return auth()->user();
    }
}

if (! function_exists('current_general_role')) {
    function current_general_role(): string
    {
        return current_user()->isStaff ? User::GENERAL_ROLE_STAFF : User::GENERAL_ROLE_CLIENT;
    }
}

if (! function_exists('current_user_is_client')) {
    function current_user_is_client(): string
    {
        return current_general_role() === User::GENERAL_ROLE_CLIENT;
    }
}

if (! function_exists('current_user_is_staff')) {
    function current_user_is_staff(): string
    {
        return current_general_role() !== User::GENERAL_ROLE_CLIENT;
    }
}

if (! function_exists('current_account')) {
    function current_account(): ?Account
    {
        return Account::current();
    }
}
