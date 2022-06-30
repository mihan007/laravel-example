<?php

namespace App\ViewModels;

use App\Domain\User\Models\User;
use Spatie\ViewModels\ViewModel;

class AccountFormViewModel extends ViewModel
{
    public function possibleAdmins()
    {
        return User::getPossibleAccountAdmin();
    }
}
