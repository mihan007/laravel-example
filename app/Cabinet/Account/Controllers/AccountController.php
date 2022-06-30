<?php

namespace App\Cabinet\Account\Controllers;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountReportRepository;
use Illuminate\Http\Request;
use App\Support\Controllers\Controller;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.index')->with(
            'data',
            [
                'accounts_count' => Account::count()
            ]
        );
    }

    public function ajaxList(Request $request)
    {
        return (
            new AccountReportRepository($request)
        )->getAndPaginate();
    }
}
