<?php

namespace Tests\Feature\Webhook;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Zadarma\Models\ZadarmaCompanyConfig;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ZadarmaTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;
    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSetting;
    /** @var \App\Domain\ProxyLead\Models\ProxyLead */
    protected $proxyLead;
    /** @var ZadarmaCompanyConfig */
    protected $zadarmaCompanyConfigs;


    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(Account::class, false);
        $this->truncate(ZadarmaCompanyConfig::class, false);

        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);
        $this->proxyLeadSetting = create(ProxyLeadSetting::class, ['company_id' => $this->company->id]);
        $this->zadarmaCompanyConfigs = create(ZadarmaCompanyConfig::class, ['company_id' => $this->company->id]);
        Event::fake();
    }

    /**
     * @test
     * @dataProvider validRequestDataProvider
     * @param array $requestData
     * @param $phone
     * @param $title
     * @param $extra
     */
    public function webhook_should_work($requestData, $phone, $title)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]),
            $requestData
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());
        $response->assertJsonFragment(['status' => 'success']);

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($title, $proxyLead->title);
        $this->assertEquals($requestData, $proxyLead->extra);
        $this->assertEquals($requestData['pbx_call_id'], $proxyLead->service_id);
    }


    /**
     * @test
     * @dataProvider noPhoneRequestDataProvider
     * @param $requestData
     */
    public function phone_is_required($requestData)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]),
            $requestData
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $response->assertJsonFragment(['status' => 'success']);

        /** @var ProxyLead $proxyLead */
        $proxyLead = ProxyLead::withTrashed()->first();
        $this->assertNull($proxyLead->deleted_at);
        $this->assertEquals($requestData, $proxyLead->extra);
        $this->assertEquals($requestData['pbx_call_id'], $proxyLead->service_id);
    }


    /**
     * @test
     */
    public function start_end_call()
    {
        $pbx_call_id = 'in_c4444ce1c12eb116b';
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]), [
            'event' => 'NOTIFY_START',
            'source' => 'zadarma-web-hook',
            'caller_id' => '+79313601777',
            'call_start' => '2020-02-01 11:40:59',
            'called_did' => '79587628385',
            'pbx_call_id' => $pbx_call_id,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());
        $response->assertJsonFragment(['status' => 'success']);

        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]), [
            "event" => "NOTIFY_END",
            "source" => "zadarma-web-hook",
            "caller_id" => "+79313601777",
            "call_start" => "2020-02-01 11:40:59",
            "called_did" => "79587628385",
            "pbx_call_id" => $pbx_call_id,
            "call_id_with_rec" => "1123123",
            "is_recorded" => 1
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());
        $response->assertJsonFragment(['status' => 'success']);
    }


    public function validRequestDataProvider()
    {
        $request['valid request'] = [
            'data' => [
                'event' => 'NOTIFY_START',
                'source' => 'zadarma-web-hook',
                'caller_id' => '+79313601777',
                'call_start' => '2020-02-01 11:40:59',
                'called_did' => '79587628385',
                'pbx_call_id' => 'in_c4444ce1c12eb116b',
            ],
            'phone' => '+79313601777',
            'title' => 'Звонок'
        ];

        $request['valid request 2'] = [
            'data' => [
                'event' => 'NOTIFY_START',
                'source' => 'zadarma-web-hook',
                'caller_id' => '+7931',
                'call_start' => '2020-02-01 11:40:59',
                'pbx_call_id' => 'in_c4444ce1c12eb116b',
            ],
            'phone' => '+7931',
            'title' => 'Звонок'
        ];


        return $request;
    }


    public function noPhoneRequestDataProvider()
    {
        $request['no phone request'] = [
            'data' => [
                'event' => 'NOTIFY_START',
                'source' => 'zadarma-web-hook',
                'call_start' => '2020-02-01 11:40:59',
                'called_did' => '79587628385',
                'pbx_call_id' => 'in_c4444ce1c12eb116b',
            ]
        ];


        return $request;
    }
}
