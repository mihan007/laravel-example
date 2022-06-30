<?php

namespace App\Api\Company\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Repositories\CompanyListRepository;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return (new CompanyListRepository(request()))->get();
    }

    public function changeLimit(Request $request)
    {
        $company = Company::query()->findOrFail($request['company_id']);
        $company->amount_limit = $request['amount_limit'];
        $company->save();

        \Artisan::call("companies:hide-leads-if-low-balance");

        return response()->json(['success' => 'success', 'value' => $company->amount_limit], 200);
    }
}
