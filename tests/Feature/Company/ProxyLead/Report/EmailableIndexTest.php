<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Tests\TestCase;

class EmailableIndexTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class);
        $this->company = Company::factory()->create();
    }

    /** @test */
    public function it_should_get_reports_for_current_month_by_default(): void
    {
        $this->signInAsSuperAdmin();

        $settings = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        ProxyLead::factory()->count(5)->create(['proxy_lead_setting_id' => $settings->id]);
        ProxyLead::factory()->count(5)->create(
            ['proxy_lead_setting_id' => $settings->id, 'created_at' => now()->subMonth(2)->toDateString()]
        );

        $response = $this->makeRequest()->json();

        $this->assertCount(5, $response['data']);
    }

    /** @test */
    public function empty_filter_will_get_all_lead_for_period(): void
    {
        $this->signInAsSuperAdmin();

        $settings = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        ProxyLead::factory()->count(5)->create(['proxy_lead_setting_id' => $settings->id]);
        ProxyLead::factory()->count(5)->create(
            ['proxy_lead_setting_id' => $settings->id, 'created_at' => now()->subMonth(2)->toDateString()]
        );

        $response = $this->makeRequest(['filter' => ''])->json();

        $this->assertCount(5, $response['data']);
    }

    private function makeRequest($data = [])
    {
        $url = route(
                'account.company.proxy-lead.report.emailable',
                [
                    'company' => $this->company,
                    'accountId' => $this->company->account_id,
                ]
            ) . '?' . http_build_query($data);

        return $this->getJson($url);
    }
}
