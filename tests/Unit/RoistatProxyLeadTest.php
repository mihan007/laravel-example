<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Tests\TestCase;

class RoistatProxyLeadTest extends TestCase
{
    /** @test */
    public function it_must_create_roistat_proxy_lead_report_after_creating_itself()
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        $config = $company->roistatConfig()->create(
            RoistatCompanyConfig::factory()->make(['company_id' => $company->id])->toArray()
        );

        /** @var RoistatProxyLead $lead */
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id]);

        $reportLead = $lead->reportLead()->first();

        $this->assertNotNull($reportLead);
        $this->assertSame($config->id, $reportLead->roistat_company_config_id, 'Config id and report lead config id are not the same');
        $this->assertSame($lead->id, $reportLead->roistat_proxy_lead_id, 'Lead id and report lead - lead id are not same');
    }

    /** @test */
    public function it_has_relation_to_company()
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id]);

        $lead->loadMissing('company');

        // remove relation that was added when lead create report lead
        unset($lead->company->roistatConfig);
        unset($lead->company->proxyLeadSettings);

        $this->assertEquals($company->id, $lead->company->id);
    }

    /**
     * @dataProvider leadStatusesDataProvider
     * @test
     */
    public function it_should_correctly_get_statuses($userConfirmation, $adminConfirmation, $statuses)
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id]);
        $lead->reportLead()
            ->first()
            ->update(['user_confirmed' => $userConfirmation, 'admin_confirmed' => $adminConfirmation]);

        $lead = $lead->fresh();

        $this->assertSame($statuses[0], $lead->is_target, 'is_target is not same');
        $this->assertSame($statuses[1], $lead->is_non_targeted, 'is_non_targeted is not same');
        $this->assertSame($statuses[2], $lead->is_not_confirmed, 'is_not_confirmed is not same');
        $this->assertSame($statuses[3], $lead->is_not_confirmed_user, 'is_not_confirmed_user is not same');
        $this->assertSame($statuses[4], $lead->is_not_confirmed_admin, 'is_not_confirmed_admin is not same');
    }

    public function leadStatusesDataProvider()
    {
        return [
            [
                RoistatProxyLeadsReport::STATUS_USER_AGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT,
                [true, false, false, false, false],
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED,
                [false, false, true, false, false],
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE,
                [false, false, true, false, false],
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_AGREE,
                [false, true, false, false, false],
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED,
                RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE,
                [false, false, true, false, false],
            ],
        ];
    }

    /**
     * @dataProvider leadPositiveStatusesDataProvider
     * @test
     */
    public function it_correct_get_positive_statuses($userConfirmation, $adminConfirmation, $stasus)
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        /** @var RoistatProxyLead $lead */
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id]);
        $lead->reportLead()
            ->first()
            ->update(['user_confirmed' => $userConfirmation, 'admin_confirmed' => $adminConfirmation]);

        $lead = $lead->fresh();

        $this->assertEquals($stasus, $lead->getPositiveStatuses()->toArray());
    }

    public function leadPositiveStatusesDataProvider()
    {
        return [
            [RoistatProxyLeadsReport::STATUS_USER_AGREE, RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT, ['is_target']],
            [RoistatProxyLeadsReport::STATUS_USER_DISAGREE, RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED, ['is_not_confirmed']],
            [RoistatProxyLeadsReport::STATUS_USER_DISAGREE, RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE, ['is_not_confirmed']],
            [RoistatProxyLeadsReport::STATUS_USER_DISAGREE, RoistatProxyLeadsReport::STATUS_ADMIN_AGREE, ['is_non_targeted']],
            [RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED, RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE, ['is_not_confirmed']],
        ];
    }

    /** @test */
    public function it_should_not_increment_target_goal_counter_if_company_has_proxy_lead_configuration(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(ProxyLeadGoalCounter::class, false);

        $company = Company::factory()->create();
        ProxyLeadSetting::factory()->create(['company_id' => $company->id]);

        $company->roistatConfig()->create(RoistatCompanyConfig::factory()->make()->toArray());
        RoistatProxyLead::factory()->create(['company_id' => $company->id]);

        $counter = ProxyLeadGoalCounter::first();

        $this->assertNull($counter);
    }

    /** @test */
    public function it_must_return_information_for_goal_counter_interface() :void
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        $company = Company::factory()->create();
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id, 'creation_date' => now()->subDay()->toDateTimeString()]);

        $this->assertSame(['company_id' => $company->id, 'for_date' => $lead->creation_date->toDateString()], $lead->getGoalCounterData());
    }

    /** @test */
    public function it_must_return_information_for_goal_counter_interface_in_spite_of_soft_deleted_company() :void
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatProxyLead::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);

        $company = Company::factory()->create();
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        $lead = RoistatProxyLead::factory()->create(['company_id' => $company->id, 'creation_date' => now()->subDay()->toDateTimeString()]);

        $company->delete();
        $this->assertSame(0, Company::all()->count());

        $this->assertSame(
            ['company_id' => $company->id, 'for_date' => $lead->creation_date->toDateString()],
            $lead->fresh()->getGoalCounterData()
        );
    }
}
