<?php

namespace Tests\Feature\Webhook;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MrqzTest extends TestCase
{

    /** @var Company */
    protected $company;
    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSetting;
    /** @var \App\Domain\ProxyLead\Models\ProxyLead */
    protected $proxyLead;


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
        Event::fake();
    }

    /**
     * @test
     * @dataProvider validRequestDataProvider
     * @param array $requestData
     * @param $phone
     * @param $name
     * @param $extra_href
     */
    public function webhook_should_work($requestData, $phone, $name, $extra_href)
    {
        $response = $this->withExceptionHandling()->postJson(route('api.v1.web-leads.common.store',
            ['key' => $this->proxyLeadSetting->public_key]),
            $requestData
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(301, $response->getStatusCode());

        $response->assertJsonMissing(['error']);

        $proxyLead = ProxyLead::first();
        $this->assertEquals($phone, $proxyLead->phone);
        $this->assertEquals($name, $proxyLead->name);
        $this->assertEquals($extra_href, $proxyLead->advertising_platform);
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

        $response->assertJsonFragment(['status' => 'error'])->assertJsonStructure(['status', 'data' => ['phone']]);

        /** @var ProxyLead $proxyLead */
        $proxyLead = ProxyLead::withTrashed()->first();
        $this->assertNotNull($proxyLead->deleted_at);
        $this->assertEquals($requestData, $proxyLead->extra);
    }


    public function validRequestDataProvider()
    {
        $request['valid request'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'test',
                    'phone' => '79001112233',
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'https://localhost.dev'
                ]
            ],
            'phone' => '79001112233',
            'name' => 'test',
            'href' => 'https://localhost.dev'
        ];

        $request['valid request 2'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'test',
                    'phone' => '0123456789',
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'test'
                ]
            ],
            'phone' => '0123456789',
            'name' => 'test',
            'href' => 'http://test'
        ];

        $request['valid request 3'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'Иван',
                    'phone' => '+7900',
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'тест'
                ]
            ],
            'phone' => '+7900',
            'name' => 'Иван',
            'href' => 'http://тест'
        ];
        return $request;
    }


    public function noPhoneRequestDataProvider()
    {
        $request['valid request'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'test'
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'http://localhost.dev'
                ]
            ]
        ];

        $request['valid request 2'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'test'
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'test'
                ]
            ]
        ];

        $request['valid request 3'] = [
            'data' => [
                'answers' => [],
                'contacts' => [
                    'name' => 'Иван'
                ],
                'quiz' => [
                    'name' => 'Иван'
                ],
                'extra' => [
                    'href' => 'тест'
                ]
            ]
        ];


        return $request;
    }

}
