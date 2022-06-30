<?php

namespace Tests\Feature\Webhook;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Zadarma\Models\ZadarmaCompanyConfig;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group AutoMatchingTest
 */
class AutoMatchingTest extends TestCase
{

    /** @var Company */
    protected $company;
    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSetting;
    /** @var ProxyLead */
    protected $proxyLead;

    /** @var ZadarmaCompanyConfig */
    protected $zadarmaCompanyConfigs;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $channel;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->truncate(Company::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(Account::class, false);
        $this->truncate(ZadarmaCompanyConfig::class, false);
        $this->truncate(Channel::class, false);

        $this->account = Account::factory()->create();
        $this->channel = Channel::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id, 'channel_id' => $this->channel->id]);
        $this->proxyLeadSetting = create(ProxyLeadSetting::class, [
            'company_id' => $this->company->id,
            'match_name' => json_encode(['name2', 'name3']),
            'match_phone' => json_encode(['phone2', 'phone3']),
            'match_info' => json_encode(['info3', 'info4']),
        ]);


        Event::fake();
    }

    /**
     * @test
     * @dataProvider validRequestDataProvider
     * @param array $requestData
     * @param $phone
     * @param $name
     * @param $info
     */
    public function webhook_should_work($requestData, $phone, $name, $info)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]),
            $requestData
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());
        $response->assertJsonFragment(['status' => 'success'])->assertJsonMissing(['error']);

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($name, $proxyLead->name);
        $this->assertEquals($info, $proxyLead->comment);
        $this->assertEquals($requestData, $proxyLead->extra);
    }

    /**
     * @test
     * @dataProvider validRequestDataProvider
     * @param array $requestData
     * @param $phone
     * @param $name
     * @param $info
     */
    public function webhook_should_work_method_get($requestData, $phone, $name, $info)
    {
        $response = $this->withExceptionHandling()->get(route('api.v1.web-leads.common.store', array_merge(['key' => $this->proxyLeadSetting->public_key],$requestData)));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());
        $response->assertJsonFragment(['status' => 'success'])->assertJsonMissing(['error']);

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($name, $proxyLead->name);
        $this->assertEquals($info, $proxyLead->comment);
        $this->assertEquals($requestData, $proxyLead->extra);
    }

    /**
     * @test
     * @dataProvider validRequestDataProvider
     * @param array $requestData
     * @param $phone
     * @param $name
     * @param $info
     */
    public function webhook_should_work_method_post($requestData, $phone, $name, $info)
    {
        $response = $this->withExceptionHandling()->post(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]),
            $requestData
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $this->assertEquals($response['status'],'success');

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($name, $proxyLead->name);
        $this->assertEquals($info, $proxyLead->comment);
        $this->assertEquals($requestData, $proxyLead->extra);
    }


    /**
     * @test
     * @dataProvider noPhoneRequestDataProvider
     * @param $requestData
     */
    public function phone_is_required($requestData)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]), $requestData);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $response->assertJsonStructure(['status', 'data' => ['phone']]);

        /** @var ProxyLead $proxyLead */
        $proxyLead = ProxyLead::withTrashed()->first();
        $this->assertNotNull($proxyLead->deleted_at);
        $this->assertEquals($requestData, $proxyLead->extra);
    }


    public function validRequestDataProvider()
    {
        $request['valid request'] = [
            'data' => [
                'name2' => 'Имя 2',
                'phone3' => 'Телефон 3',
                'info3' => 'Информация 3',
            ],
            'phone' => 'Телефон 3',
            'name' => 'Имя 2',
            'info' => 'Информация 3'
        ];

        $request['valid request 2'] = [
            'data' => [
                'name3' => 'Имя 3',
                'phone3' => 'Телефон 3',
                'info4' => 'Информация 4',
            ],
            'phone' => 'Телефон 3',
            'name' => 'Имя 3',
            'info' => 'Информация 4'
        ];


        return $request;
    }


    public function noPhoneRequestDataProvider()
    {
        $request['no phone request'] = [
            'data' => [
                'name3' => 'Имя 3',
                'phone222' => 'Телефон 3',
                'info4' => 'Информация 4',
            ]
        ];


        return $request;
    }
}