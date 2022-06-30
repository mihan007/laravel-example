<?php

namespace Tests\Feature\Company\Report;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UpdateReportTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class);
        $this->truncate(RoistatProxyLeadsReport::class);
        $this->company = Company::factory()->create();
        $this->config = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
    }

    /** @test */
    public function we_can_save_report_lead_without_setting_deleted(): void
    {
        $this->signInAsSuperAdmin();

        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = RoistatProxyLeadsReport::factory()->create(
            ['roistat_company_config_id' => $this->config->id, 'roistat_proxy_lead_id' => $lead->id]
        );

        $this->makeRequest(['report' => [['id' => $report->id, 'title' => 'New title', 'deleted' => null]]]);

        $this->assertDatabaseHas('roistat_proxy_leads_reports', ['id' => $report->id, 'title' => 'New title']);
    }

    /** @test */
    public function it_will_set_user_not_confirmed_status_if_admin_disagree(): void
    {
        $this->signInAsSuperAdmin();

        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);

        $lead->loadMissing('reportLead');
        $lead->reportLead->user_confirmed = RoistatProxyLeadsReport::STATUS_USER_DISAGREE;
        $lead->reportLead->admin_confirmed = RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED;
        $lead->reportLead->save();

        $report = $lead->reportLead->fresh();

        $this->makeRequest(
            [
                'report' => [
                    [
                        'id' => $report->id,
                        'admin_confirmed' => RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE,
                        'deleted' => null
                    ]
                ],
            ]
        );

        $report = $lead->reportLead->fresh();

        $this->assertSame(RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED, $report->user_confirmed);
        $this->assertSame(RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE, $report->admin_confirmed);
    }

    private function makeRequest($data = []): TestResponse
    {
        return $this->postJson(
            route(
                'account.company.report.update',
                [
                    'id' => $this->company->id,
                    'accountId' => $this->company->account_id,
                ]
            ),
            $data
        );
    }
}
