<?php

namespace Tests\Unit;

use App\Domain\Company\AutoApproval;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\PlApprovedReport;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Models\ApprovedReport;
use Tests\TestCase;

class AutoApprovalTest extends TestCase
{
    /** @test */
    public function it_should_approve_company_with_roistat_company_config_and_without_leads() :void
    {
        $this->truncate(ApprovedReport::class, false);
        $this->truncate(Company::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(ProxyLeadSetting::class, false);

        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);

        $this->assertSame(0, ApprovedReport::count());

        (new AutoApproval(now()->startOfMonth()))->check();

        $this->assertSame(1, ApprovedReport::count());
    }

    /** @test */
    public function it_should_approve_company_with_proxy_lead_settings_and_without_leads(): void
    {
        $this->truncate(PlApprovedReport::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(Company::class, false);

        $company = Company::factory()->create();
        ProxyLeadSetting::factory()->create(['company_id' => $company->id]);

        $this->assertSame(0, PlApprovedReport::count());

        (new AutoApproval(now()->startOfMonth()))->check();

        $this->assertSame(1, PlApprovedReport::count());
    }
}
