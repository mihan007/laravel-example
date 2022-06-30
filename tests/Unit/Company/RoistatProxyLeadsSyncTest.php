<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Tests\TestCase;

class RoistatProxyLeadsSyncTest extends TestCase
{
    /** @test */
    public function sync_new_leads_for_one_company()
    {
        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatProxyLeadsReport::class, false);

        $company = Company::factory()->create();
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        RoistatProxyLead::flushEventListeners();
        $leads = RoistatProxyLead::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertEquals(0, RoistatProxyLeadsReport::getQuery()->count());

        (new \App\Domain\Company\RoistatProxyLeadsSync())->sync();

        $this->assertEquals(3, RoistatProxyLeadsReport::getQuery()->count());
    }

    /** @test */
    public function sync_company_without_roistat_company_config_will_not_sync_anything()
    {
        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatProxyLeadsReport::class, false);

        $company = Company::factory()->create();
        // I know that company without config can't have any leads, but we testing that synchronizer will never
        // try to create report lead
        RoistatProxyLead::flushEventListeners();
        $leads = RoistatProxyLead::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertEquals(0, RoistatProxyLeadsReport::getQuery()->count());

        (new \App\Domain\Company\RoistatProxyLeadsSync())->sync();

        $this->assertEquals(0, RoistatProxyLeadsReport::getQuery()->count());
    }

    /** @test */
    public function if_company_has_few_leads_sync_will_add_only_missing_leads()
    {
        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatProxyLeadsReport::class, false);

        $company = Company::factory()->create();
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        RoistatProxyLead::factory()->count(3)->create(['company_id' => $company->id]);
        RoistatProxyLead::flushEventListeners();
        RoistatProxyLead::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertEquals(3, RoistatProxyLeadsReport::getQuery()->count());

        (new \App\Domain\Company\RoistatProxyLeadsSync())->sync();

        $this->assertEquals(6, RoistatProxyLeadsReport::getQuery()->count());
    }

    /** @test */
    public function it_correctly_sync_more_than_limit_leads()
    {
        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatProxyLeadsReport::class, false);

        $company = Company::factory()->create();
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        RoistatProxyLead::flushEventListeners();
        $leads = RoistatProxyLead::factory()->count(105)->create(['company_id' => $company->id]);

        $this->assertEquals(0, RoistatProxyLeadsReport::getQuery()->count());

        (new \App\Domain\Company\RoistatProxyLeadsSync())->sync();

        $this->assertEquals(105, RoistatProxyLeadsReport::getQuery()->count());
    }

    /** @test */
    public function it_correctly_sync_few_companies()
    {
        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatProxyLeadsReport::class, false);

        RoistatProxyLead::flushEventListeners();

        $company1 = Company::factory()->create();
        $config1 = RoistatCompanyConfig::factory()->create(['company_id' => $company1->id]);
        $leads1 = RoistatProxyLead::factory()->count(3)->create(['company_id' => $company1->id]);

        $company2 = Company::factory()->create();
        $config2 = RoistatCompanyConfig::factory()->create(['company_id' => $company2->id]);
        $leads2 = RoistatProxyLead::factory()->count(3)->create(['company_id' => $company2->id]);

        $this->assertEquals(0, RoistatProxyLeadsReport::getQuery()->count());

        (new \App\Domain\Company\RoistatProxyLeadsSync())->sync();

        $this->assertEquals(6, RoistatProxyLeadsReport::getQuery()->count());
    }
}
