<?php

namespace Tests\Unit;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use Tests\TestCase;

/**
 * Class CreateProxyLeadSettingsTest.
 */
class CreateProxyLeadSettingsTest extends TestCase
{
    /** @test */
    public function guest_can_not_create_proxy_lead_settings()
    {
        $account = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $account->id]);

        $this->withExceptionHandling()
            ->post(route('account.company.proxy-leads.store', ['company' => $company, 'accountId' => $account->id]))
            ->assertRedirect('/login');
    }

    /** @test */
    public function user_can_create_proxy_lead_settings()
    {
        $this->markTestSkipped('Skipped until http://ldgv2.herokuapp.com/settings/orders-source will be done');
        $account = Account::factory()->create();
        $this->signIn(null, 'admin', $account);
        $company = Company::factory()->create(['account_id' => $account->id]);

        $response = $this->post(route('account.company.proxy-leads.store', ['company' => $company, 'accountId' => $account->id]));

        $this->get($response->headers->get('Location'))
            ->assertSee($company->proxyLeadSettings->public_key);
    }
}
