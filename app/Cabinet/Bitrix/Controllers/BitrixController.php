<?php

namespace App\Cabinet\Bitrix\Controllers;

use App\Domain\Company\Models\Company;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BitrixController extends Controller
{
    public function store(Request $request, $accountId, Company $company)
    {
        $proxyLeadSettings = $company->proxyLeadSettings()->first();

        if ($proxyLeadSettings === null) {
            return redirect()->route('account.company.proxy-leads', ['company' => $company]);
        }

        if (! empty($proxyLeadSettings->bitrix_webhook)) {
            $proxyLeadSettings->bitrix_webhook = null;
        } else {
            $validator = Validator::make($request->all(), [
                'birtix_webhook' => 'required|url',
            ]);
            $birtix_webhook = ! $validator->fails() ? $request['birtix_webhook'] : null;

            $proxyLeadSettings->bitrix_webhook = $birtix_webhook;
        }

        $proxyLeadSettings->save();

        return redirect()->route('account.company.crm-integration.index', ['company' => $company]);
    }
}
