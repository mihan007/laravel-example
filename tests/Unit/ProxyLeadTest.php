<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Tests\TestCase;

/**
 * Class ProxyLeadTest.
 * @group current
 */
class ProxyLeadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(ProxyLeadGoalCounter::class, false);
        $this->truncate(PlReportLead::class, false);
    }

    /** @test */
    public function it_must_soft_delete()
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $lead */
        $lead = ProxyLead::factory()->create();

        $this->assertDatabaseHas('proxy_leads', ['id' => $lead->id, 'deleted_at' => null]);

        $lead->delete();

        $this->assertDatabaseHas('proxy_leads', ['id' => $lead->id]);
    }

    /** @test */
    public function it_can_restored()
    {
        ProxyLead::flushEventListeners();
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $lead */
        $lead = ProxyLead::factory()->create();

        $lead->delete();
        $lead->fresh()->restore();

        $this->assertDatabaseHas('proxy_leads', ['id' => $lead->id, 'deleted_at' => null]);
    }

    /** @test */
    public function it_has_one_report_lead()
    {
        $lead = ProxyLead::factory()->create();
        $reportLead = PlReportLead::factory()->create(['proxy_lead_id' => $lead->id]);

        $this->assertTrue($lead->reportLead()->get()->contains($reportLead));
    }

    /** @test */
    public function creating_proxy_lead_also_creates_report_lead()
    {
        $lead = ProxyLead::factory()->create();

        $this->assertEquals(1, $lead->reportLead()->count());
    }

    /** @test */
    public function deleting_proxy_lead_also_delete_report_lead()
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $lead */
        $lead = ProxyLead::factory()->create();

        $this->assertDatabaseHas('pl_report_leads', ['proxy_lead_id' => 1, 'deleted_at' => null]);

        $lead->delete();

        $this->assertDatabaseHas('pl_report_leads', ['proxy_lead_id' => 1]);
    }

    /** @test */
    public function it_can_attach_company()
    {
        $company = Company::factory()->create();
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $company->id]);
        $proxyLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $proxyLeadSetting->id]);

        $proxyLead->load('proxyLeadSetting.company');

        $this->assertEquals($proxyLead->proxyLeadSetting->company->name, $company->name);
    }

    /** @test */
    public function restoring_lead_will_restore_report_lead()
    {
        $lead = ProxyLead::factory()->create()->loadMissing('reportLead');
        $reportLead = $lead->reportLead;

        $lead->delete();

        $this->assertTrue($reportLead->fresh()->trashed());

        $lead->fresh()->restore();

        $this->assertFalse($reportLead->fresh()->trashed());
    }

    /** @test */
    public function main_target_status_gets_correct()
    {
        $lead = ProxyLead::factory()->create(['phone' => '12312312'.random_int(343, 9999)]);

        $this->assertTrue($lead->is_target);
    }

    /** @test */
    public function non_target_status_gets_correct()
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $reportLead */
        $lead = ProxyLead::factory()->create();
        $lead->loadMissing('reportLead');
        $this->assertTrue($lead->is_target);

        $lead->reportLead
            ->userConfirmation(PlReportLead::STATUS_DISAGREE)
            ->adminConfirmation(PlReportLead::STATUS_AGREE);

        $this->assertFalse($lead->is_target);
        $this->assertTrue($lead->is_non_targeted);
        $this->assertFalse($lead->is_not_confirmed);
        $this->assertFalse($lead->is_not_confirmed_user);
        $this->assertFalse($lead->is_not_confirmed_admin);
    }

    /** @test */
    public function admin_not_confirmed_status_gets_correct()
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $reportLead */
        $lead = ProxyLead::factory()->create();
        $lead->loadMissing('reportLead');

        $lead->reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE);

        $this->assertTrue($lead->is_not_confirmed_admin);

        $lead->reportLead->adminConfirmation(PlReportLead::STATUS_DISAGREE);

        $this->assertFalse($lead->is_not_confirmed_admin);
    }

    /** @test */
    public function it_can_get_positive_statuses()
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $lead */
        $lead = ProxyLead::factory()->create()->loadMissing('reportLead');

        $this->assertSame(['is_target'], $lead->getPositiveStatuses()->toArray());

        $lead->reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE);

        $this->assertSame(['is_non_targeted', 'is_not_confirmed_admin'], $lead->getPositiveStatuses()->toArray());

        $lead->reportLead->adminConfirmation(PlReportLead::STATUS_AGREE);

        $this->assertSame(['is_non_targeted'], $lead->getPositiveStatuses()->toArray());
    }

    public function goalCounterDeleteDataProvider()
    {
        return [
            [null, null, [1, 0, 0, 0, 0]],
            [PlReportLead::STATUS_DISAGREE, null, [0, 0, 1, 0, 1]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_AGREE, [0, 1, 0, 0, 0]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_DISAGREE, [0, 0, 1, 1, 0]],
        ];
    }

    public function goalCounterUpdateDataProvider()
    {
        return [
            [PlReportLead::STATUS_DISAGREE, null, [0, 0, 1, 0, 1]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_AGREE, [0, 1, 0, 0, 0]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_DISAGREE, [0, 0, 1, 1, 0]],
        ];
    }

    public function goalCounterRestoringDataProvider()
    {
        return [
            [null, null, [1, 0, 0, 0, 0]],
            [PlReportLead::STATUS_DISAGREE, null, [0, 0, 1, 0, 1]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_AGREE, [0, 1, 0, 0, 0]],
            [PlReportLead::STATUS_DISAGREE, PlReportLead::STATUS_DISAGREE, [0, 0, 1, 1, 0]],
        ];
    }
}
