<?php

namespace App\Cabinet\Bitrix\Controllers;

use App\Domain\Company\Models\Company;
use App\Support\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

/**
 * Class CRMIntegrationController.
 */
class CRMIntegrationController extends Controller
{
    /**
     * @param \App\Domain\Company\Models\Company $company
     * @return Response
     */
    public function index($accountId, Company $company)
    {
        $company->load('proxyLeadSettings');
        $data['company'] = collect($company->toArray())->only(['id', 'public_id', 'name'])->toArray();
        $data['proxyLeadSettings'] = Arr::get($company->toArray(), 'proxy_lead_settings', []);

        return view('pages.company.crm-integration.index')->with('data', $data);
    }
}
