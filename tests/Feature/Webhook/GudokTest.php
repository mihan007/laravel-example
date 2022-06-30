<?php

namespace Tests\Feature\Webhook;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Zadarma\Models\ZadarmaCompanyConfig;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GudokTest extends TestCase
{

    /** @var Company */
    protected $company;
    /** @var ProxyLeadSetting */
    protected $proxyLeadSetting;
    /** @var ProxyLead */
    protected $proxyLead;

    /** @var \App\Domain\Zadarma\Models\ZadarmaCompanyConfig */
    protected $zadarmaCompanyConfigs;


    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(Account::class, false);

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
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store', ['key' => $this->proxyLeadSetting->public_key]), $requestData);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $response->assertJsonMissing(['error']);

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($title, $proxyLead->title);

        $this->assertEquals([
            'link' => $requestData['audio'],
            'status' => 'GOODOK_' . $requestData['callstatus'],
            'duration' => $requestData['billsec']
        ], $proxyLead->extra);
    }


    /**
     * @test
     * @dataProvider noPhoneRequestDataProvider
     * @param $requestData
     */
    public function phone_is_required($requestData)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store', ['key' => $this->proxyLeadSetting->public_key]), $requestData);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $response->assertJsonFragment(['status' => 'error'])->assertJsonStructure(['status', 'data' => ['phone']]);

        /** @var \App\Domain\ProxyLead\Models\ProxyLead $proxyLead */
        $proxyLead = ProxyLead::withTrashed()->first();
        $this->assertNotNull($proxyLead->deleted_at);
        $this->assertEquals($requestData, $proxyLead->extra);
    }


    public function validRequestDataProvider()
    {
        $request['valid request'] = [
            'data' => [
                'id' => 721,
                'project_id' => 11,
                'project_title' => 'Мой первый проект',
                'dst' => '73365841970',
                'adv_channel_id' => '50',
                'adv_channel_name' => 'Название моего номера',
                'src' => '74956455366',
                'duration' => 23,
                'billsec' => 13,
                'callstatus' => 'ANSWERED',
                'date' => '2020-08-13 08:29:03 UTC',
                'region' => 'Москва',
                'call_number' => 1,
                'audio' => 'https://api.zadarma.com/v1/pbx/record/download/dcfd4d9593b86b46fad5868c0105e667465f812443fbb0f7f1d5efafb85ad5d0/b469a82a25f8f434feaa5c0e9421b12911c89e5a6d66a194292bb810ec3e0747/244834-1598346610.55927-79183926525-2020-08-25-121010.mp3',
            ],
            'phone' => '74956455366',
            'title' => 'Звонок'
        ];


        $request['valid request 2'] = [
            'data' => [
                'id' => '0',
                'project_id' => '0',
                'adv_channel_id' => '0',
                'adv_channel_name' => '0',
                'duration' => '60',
                'billsec' => '30',
                'callstatus' => 'ANSWERED',
                'src' => '79999999999',
                'dst' => '79999999999',
                'date' => '2020-09-02 05:30:10 UTC',
                'region' => NULL,
                'call_number' => '1',
                'project_title' => 'Test',
                'audio' => 'https://in.gudok.tel/api/audio/test',
            ],
            'phone' => '79999999999',
            'title' => 'Звонок'
        ];

        return $request;
    }


    public function noPhoneRequestDataProvider()
    {
        $request['no phone request'] = [
            'data' => [
                'id' => 721,
                'project_id' => 11,
                'project_title' => 'Мой первый проект',
                'dst' => '73365841970',
                'adv_channel_id' => '50',
                'adv_channel_name' => 'Название моего номера',
                'duration' => 23,
                'billsec' => 13,
                'callstatus' => 'ANSWERED',
                'date' => '2020-08-13 08:29:03 UTC',
                'region' => 'Москва',
                'call_number' => 1,
                'audio' => 'https://api.zadarma.com/v1/pbx/record/download/dcfd4d9593b86b46fad5868c0105e667465f812443fbb0f7f1d5efafb85ad5d0/b469a82a25f8f434feaa5c0e9421b12911c89e5a6d66a194292bb810ec3e0747/244834-1598346610.55927-79183926525-2020-08-25-121010.mp3',
            ]
        ];

        $request['no phone request'] = [
            'data' => [
                'id' => '0',
                'project_id' => '0',
                'adv_channel_id' => '0',
                'adv_channel_name' => '0',
                'duration' => '60',
                'billsec' => '30',
                'callstatus' => 'ANSWERED',
                'dst' => '79999999999',
                'date' => '2020-09-02 05:30:10 UTC',
                'region' => NULL,
                'call_number' => '1',
                'project_title' => 'Test',
                'audio' => 'https://in.gudok.tel/api/audio/test',
            ]
        ];


        return $request;
    }
}
