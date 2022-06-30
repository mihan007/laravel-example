<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @var Company */
    protected $company;

    /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig */
    protected $config;

    protected function setUp(): void
    {
        $this->markTestSkipped('Enable when we do client proxy lead page');

        parent::setUp();

        $this->truncate(Company::class);
        $this->truncate(RoistatCompanyConfig::class);
        $this->truncate(RoistatProxyLead::class);
        $this->company = Company::factory()->create();
        $this->config = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
    }

    /** @test */
    public function anyone_can_access_the_route(): void
    {
        $this->makeRequest()->assertStatus(302)->assertRedirect("/user/{$this->company->public_id}/report");
    }

    /** @test */
    public function it_will_show_404_page_if_public_key_is_invalid(): void
    {
        $this->withExceptionHandling()->put('/user/invalid/report')->assertStatus(404);
    }

    /**
     * @dataProvider invalidKeysDataProvider
     * @test
     * @param $key
     * @param $newValue
     */
    public function it_will_not_update_fields_that_is_not_valid($key, $newValue): void
    {
        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = $lead->load('reportLead')->reportLead;

        $this->makeRequest(['report' => [
            ['id' => $report->id, 'user_confirmed' => RoistatProxyLeadsReport::STATUS_USER_AGREE, $key => $newValue],
        ]]);

        $this->assertSame($report->$key, $report->fresh()->$key);
    }

    public function invalidKeysDataProvider()
    {
        return [
            ['title', 'new title'],
            ['text', 'new text'],
            ['name', 'new name'],
            ['phone', 'new phone'],
            ['email', 'new email'],
            ['roistat', 'new roistat'],
            ['creation_date', 'new creation date'],
            ['order_id', 'new order id'],
            ['for_date', '2018-01-01'],
            ['deleted', '1'],
            ['admin_comment', 'new comment'],
        ];
    }

    /** @test */
    public function it_will_not_fail_if_user_confirmation_is_not_set_in_query(): void
    {
        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = $lead->load('reportLead')->reportLead;

        $this->makeRequest(['report' => [['id' => $report->id]]])->assertStatus(302);
    }

    /** @test */
    public function it_will_not_fail_if_user_comment_is_not_set_in_query(): void
    {
        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = $lead->load('reportLead')->reportLead;

        $this->makeRequest(['report' => [['id' => $report->id, 'user_confirmed' => RoistatProxyLeadsReport::STATUS_USER_AGREE]]])
            ->assertStatus(302);
    }

    /** @test */
    public function if_request_has_period_it_will_redirect_with_period(): void
    {
        $this->makeRequest(['period' => '2018-04-01'])
            ->assertRedirect("/user/{$this->company->public_id}/report?period=2018-04-01");
    }

    /**
     * @dataProvider changeUserStatusDataProvider
     * @test
     * @param $userStatus
     * @param $adminStatus
     * @param $newAdminStatus
     */
    public function it_should_change_status_correctly($userStatus, $adminStatus, $newAdminStatus): void
    {
        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = $lead->load('reportLead')->reportLead;

        $report->admin_confirmed = $adminStatus;
        $report->save();

        $this->makeRequest(['report' => [['id' => $report->id, 'user_confirmed' => $userStatus, 'user_comment' => 'some']]]);

        $report = $report->fresh();

        $this->assertSame($report->admin_confirmed, $newAdminStatus);
        $this->assertSame($report->user_confirmed, $userStatus);
    }

    public function changeUserStatusDataProvider()
    {
        return [
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT,
                RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED,
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_AGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT,
                RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT,
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_AGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_AGREE,
            ],
            [
                RoistatProxyLeadsReport::STATUS_USER_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE,
                RoistatProxyLeadsReport::STATUS_ADMIN_NOT_CONFIRMED,
            ],
        ];
    }

    /** @test */
    public function it_should_change_comment(): void
    {
        $lead = RoistatProxyLead::factory()->create(['company_id' => $this->company->id]);
        $report = $lead->load('reportLead')->reportLead;

        $report->admin_confirmed = RoistatProxyLeadsReport::STATUS_ADMIN_DEFAULT;
        $report->save();

        $this->makeRequest(['report' => [['id' => $report->id, 'user_confirmed' => RoistatProxyLeadsReport::STATUS_USER_DISAGREE, 'user_comment' => 'New comment']]]);

        $report = $report->fresh();

        $this->assertSame('New comment', $report->user_comment);
    }

    private function makeRequest($data = [])
    {
        return $this->put("/user/{$this->company->public_id}/report", $data);
    }
}
