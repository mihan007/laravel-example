<?php

namespace Tests\Unit;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Tests\TestCase;

/**
 * Class DeleteEmailableLeadTest.
 */
class DeleteEmailableLeadTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;
    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSetting;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Account::class);
        $this->truncate(Company::class);
        $this->truncate(ProxyLeadSetting::class);
        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);
        $this->proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
    }

    /** @test */
    public function it_should_trashed_delete_emailable_lead(): void
    {
        $this->signIn(null, 'admin', $this->account);

        /** @var ProxyLead $proxyLead */
        $proxyLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSetting->id]);

        $this->assertFalse($proxyLead->trashed());

        $this->makeRequest($proxyLead);

        $this->assertTrue($proxyLead->fresh()->trashed());
    }

    /** @test */
    public function it_should_restore_deleted_emailable_lead(): void
    {
        $this->signIn(null, 'admin', $this->account);

        /** @var \App\Domain\ProxyLead\Models\ProxyLead $proxyLead */
        $proxyLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSetting->id]);
        $proxyLead->delete();

        $this->assertTrue($proxyLead->fresh()->trashed());

        $this->makeRequest($proxyLead);

        $this->assertFalse($proxyLead->fresh()->trashed());
    }

    private function makeRequest(ProxyLead $lead)
    {
        $url = route(
            'account.company.proxy-lead.report.emailable.destroy',
            ['company' => $this->company, 'proxyLead' => $lead->id, 'accountId' => $this->account->id]
        );

        return $this->delete($url);
    }
}
