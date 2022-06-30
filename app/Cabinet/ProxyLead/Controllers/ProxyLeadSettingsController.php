<?php

namespace App\Cabinet\ProxyLead\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProxyLeadSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index($accountId, Company $company)
    {
        $data['company'] = collect($company->toArray())->only(['id', 'public_id', 'name'])->toArray();
        $company->load('proxyLeadSettings');
        if (! $company->proxyLeadSettings) {
            $company->proxyLeadSettings()->create([
                'public_key' => Str::random(20),
            ]);
            $company->load('proxyLeadSettings');
        }

        $data['proxyLeadSettings'] = Arr::get($company->toArray(), 'proxy_lead_settings', []);
        $data['proxyLeadSettings']['match_phone'] = json_decode($data['proxyLeadSettings']['match_phone'], 1);
        $data['proxyLeadSettings']['match_name'] = json_decode($data['proxyLeadSettings']['match_name'], 1);
        $data['proxyLeadSettings']['match_info'] = json_decode($data['proxyLeadSettings']['match_info'], 1);

        return view('pages.company.proxy-leads.index')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Company $company
     * @return \Illuminate\Http\Response
     */
    public function store($accountId, Company $company)
    {
        $company->proxyLeadSettings()->create([
            'public_key' => Str::random(20),
        ]);

        return redirect()->route('account.company.proxy-leads', ['company' => $company]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domain\ProxyLead\Models\ProxyLeadSetting  $proxyLeadSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ProxyLeadSetting $proxyLeadSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain\ProxyLead\Models\ProxyLeadSetting  $proxyLeadSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ProxyLeadSetting $proxyLeadSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $company_id
     * @param $proxy_lead_id
     * @return void
     */
    public function update(Request $request, $account_id, $company_id, $proxy_lead_id)
    {
        $proxyLeadSetting = ProxyLeadSetting::find($proxy_lead_id);
        $proxyLeadSetting->match_name = json_encode($request->post('match_name'));
        $proxyLeadSetting->match_phone = json_encode($request->post('match_phone'));
        $proxyLeadSetting->match_info = json_encode($request->post('match_info'));

        $proxyLeadSetting->save();

        return redirect()->route('account.company.proxy-leads', ['company' => $company_id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain\ProxyLead\Models\ProxyLeadSetting  $proxyLeadSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProxyLeadSetting $proxyLeadSetting)
    {
        //
    }
}
