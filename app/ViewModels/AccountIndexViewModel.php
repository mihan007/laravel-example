<?php

namespace App\ViewModels;

use App\Domain\User\Models\User;
use Spatie\ViewModels\ViewModel;

class AccountIndexViewModel extends ViewModel
{
    private $accounts;

    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    public function accounts()
    {
        return $this->accounts;
    }
}
