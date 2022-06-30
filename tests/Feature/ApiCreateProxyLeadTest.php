<?php

namespace Tests\Unit;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Tests\TestCase;

/**
 * Class ApiCreateProxyLeadTest.
 */
class ApiCreateProxyLeadTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    /** @var ProxyLeadSetting */
    protected $proxyLeadSettings;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);

        $this->proxyLeadSettings = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
    }

    /** @test */
    public function anyone_can_create_proxy_leads()
    {
        $requestVars = ProxyLead::factory()->make()->toArray();
        $requestVars['api_key'] = $this->proxyLeadSettings->public_key;

        $this->get($this->getStoreLink($requestVars))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $requestVars['title']]);

        $this->post($this->getStoreLink($requestVars))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $requestVars['title']]);
    }

    /** @test */
    public function required_phone_number()
    {
        $this->sendProxyLeadToServer(['phone' => null])
            ->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['phone']])
            ->assertJson(['status' => 'error']);
    }

    /**
     * @test
     */
    public function phone_number_with_numbers_must_be_store_exactly()
    {
        $phone = '+7 (987) 992-99-99';
        $this->sendProxyLeadToServer(['phone' => ''.$phone.''])
            ->assertStatus(200)
            ->assertJsonFragment(['phone' => $phone]);
    }

    /** @test */
    public function phone_number_with_string_must_be_store_exactly()
    {
        $stringPhone = 'some invalid phone';
        $this->sendProxyLeadToServer(['phone' => $stringPhone])
            ->assertStatus(200)
            ->assertJsonFragment(['phone' => $stringPhone]);
    }

    /** @test */
    public function required_api_key()
    {
        $this->withExceptionHandling()
            ->sendProxyLeadToServer(['api_key' => null])
            ->assertStatus(400);
    }

    /** @test */
    public function required_valid_api_key()
    {
        $this->withExceptionHandling()
            ->sendProxyLeadToServer(['api_key' => 'someinvalidkey'])
            ->assertStatus(404);
    }

    /** @test */
    public function title_can_be_empty()
    {
        $this->sendProxyLeadToServer(['title' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function name_can_be_empty()
    {
        $this->sendProxyLeadToServer(['name' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function comment_can_be_empty()
    {
        $this->sendProxyLeadToServer(['comment' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function ym_counter_can_be_empty()
    {
        $this->sendProxyLeadToServer(['ym_counter' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function tag_can_be_empty(): void
    {
        $this->sendProxyLeadToServer(['tag' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function we_can_set_tag(): void
    {
        $this->sendProxyLeadToServer(['tag' => 'some_tag'])
            ->assertStatus(200)
            ->assertJsonFragment(['tag' => 'some_tag']);
    }

    /** @test */
    public function advertising_platform_can_be_empty(): void
    {
        $this->sendProxyLeadToServer(['advertising_platform' => null])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }

    /** @test */
    public function we_can_set_advertising_platform(): void
    {
        $this->sendProxyLeadToServer(['advertising_platform' => 'some_tag'])
            ->assertStatus(200)
            ->assertJsonFragment(['advertising_platform' => 'http://some_tag']);
    }

    protected function sendProxyLeadToServer($overrides = [])
    {
        $requestVars = ProxyLead::factory()->make($overrides)->toArray();

        $requestVars['api_key'] = array_key_exists('api_key', $overrides) ?
            $overrides['api_key'] :
            $this->proxyLeadSettings->public_key;

        return $this->get($this->getStoreLink($requestVars));
    }

    /**
     * Get store proxy lead link.
     *
     * @param $companyId
     * @param array $params
     * @return string
     */
    protected function getStoreLink($params = [])
    {
        return route('api.v1.company.proxy-lead.store').'?'.http_build_query($params);
    }
}
