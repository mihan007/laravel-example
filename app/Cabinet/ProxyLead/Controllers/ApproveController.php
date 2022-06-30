<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 20.07.2018
 * Time: 9:26.
 */

namespace App\Cabinet\ProxyLead\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\FinanceReportCreator;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApproveController extends Controller
{
    public function store(Request $request, $accountId, Company $company)
    {
        $this->validate($request, [
            'for_date' => 'required|date',
        ]);

        $company->load('proxyLeadSettings');

        if (null === $company->proxyLeadSettings) {
            return response()->json(
                [
                    'status' => 'error',
                    'data' => ['message' => 'У компании "'.$company->name.'" не активно прокси лидирование.'],
                ],
                422
            );
        }

        $period = Carbon::parse($request->get('for_date'))->startOfMonth();

        $approve = $company->proxyLeadSettings->approvedReports()->firstOrCreate(['for_date' => $period->toDateString()]);

        (new FinanceReportCreator($company, $period))->create();

        return $approve;
    }

    public function show($accountId, Company $company, $period)
    {
        $company->load('proxyLeadSettings');

        if (null === $company->proxyLeadSettings) {
            return response()->json(
                [
                    'status' => 'error',
                    'data' => ['message' => 'У компании "'.$company->name.'" не активно прокси лидирование.'],
                ],
                422
            );
        }

        return $company->proxyLeadSettings->approvedReports()->where('for_date', $period)->first();
    }
}
