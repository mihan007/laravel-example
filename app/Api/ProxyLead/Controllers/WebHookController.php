<?php

namespace App\Api\ProxyLead\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Balance;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\ProxyLead\DuplicateChecker;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Events\WrongProxyLeadPayloadEvent;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\Services\LeadService;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebHookController extends Controller
{
    /**
     * Сервис для обработки лидов.
     * @var \App\Domain\ProxyLead\Services\LeadService
     */
    private $leadService;

    /**
     * WebHookController constructor.
     * @param LeadService $leadService
     */
    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Domain\Company\Models\Company $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $key = $request->key ?? $request->api_key;

        // @TODO public_key attach company!!!!
        $data = ['extra' => $request->all()];

        logger()->info("New web-hook lead came key={$key};", $data);

        /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSettings */
        $proxyLeadSettings = ProxyLeadSetting::where('public_key', $key)->firstOrFail();

        //проведем данные запроса через автоматчинг
        $data = $this->leadService->autoMatchLeadData($proxyLeadSettings, $data['extra']);
        $data['extra'] = $request->all();

        $data['api_key'] = $key;

        foreach (['caller' => 'phone'] as $exists_key => $new_key) {
            if (array_key_exists($exists_key, $data['extra'])) {
                $data[$new_key] = $data['extra'][$exists_key];
                unset($data['extra'][$exists_key]);
            }
        }

        /* Обработка входных полей */
        if (empty($data['extra']['link'])) {
            $data['advertising_platform'] = preg_replace('/[\?\#].*/', '', ($data['extra']['landing_page'] ?? ''));
        }

        /** @link https://roistat.api-docs.io/v1/kolltreking/WtdtaeEJQozsgmeg4 */
        $data = array_merge($request->all(), $data);

        //проставнока данных полученных от ройстата
        if (! empty($data['extra']['duration']) && empty($data['title'])) {
            $data['title'] = 'Звонок';
            $data['source'] = 'call-web-hook';
        }

        $data['title'] = $data['title'] ?? '';

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($data, [
                'api_key' => 'required|exists:proxy_lead_settings,public_key',
                'phone' => 'required',
                'title' => 'nullable',
                'name' => 'nullable',
                'comment' => 'nullable',
                'ym_counter' => 'nullable',
                'tag' => 'nullable',
                'advertising_platform' => 'nullable',
                'extra' => 'nullable|array',
            ]
        );

        if ($validator->fails()) {
            $this->handleUnexpectedLeadData($proxyLeadSettings, $data);

            logger()->error('Error validate web-hook data. '.$validator->messages().'  key={$key};', $data);

            return response()->json(['status' => 'error', 'data' => $validator->messages()], 200);
        }

        $company = Company::find($proxyLeadSettings->company_id);
        $data['is_free'] = $company->free_period;
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $proxyLead */
        $proxyLead = $proxyLeadSettings->proxyLeads()->create($data);
        $hasDuplicates = (new DuplicateChecker($proxyLead))->check();
        if ($company) {
            new Balance($company, $proxyLead, $hasDuplicates);
            new BalanceNotifier($company);
        }

        event(new CreateProxyLeadEvent($proxyLeadSettings, $proxyLead));

        return response()->json(['status' => 'success', 'data' => $proxyLead]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @return \Illuminate\Http\Response
     */
    public function show(ProxyLead $proxyLead)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @return \Illuminate\Http\Response
     */
    public function edit(ProxyLead $proxyLead)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProxyLead $proxyLead)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProxyLead $proxyLead)
    {
        //
    }

    protected function getNumericString($str)
    {
        preg_match_all('!\d+!', $str, $matches);

        return implode('', $matches[0]);
    }

    /**
     * Ловим нераспознанный лид, и отправляем его администратору.
     *
     * @param $proxyLeadSettings
     * @param $data
     */
    private function handleUnexpectedLeadData($proxyLeadSettings, $data): void
    {
        $company = $proxyLeadSettings->company;

        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['is_free'] = $company->free_period;

        /** @var \App\Domain\ProxyLead\Models\ProxyLead $proxyLead */
        $proxyLead = $proxyLeadSettings->proxyLeads()->create($data);

        event(new WrongProxyLeadPayloadEvent($proxyLeadSettings, $proxyLead));
    }
}
