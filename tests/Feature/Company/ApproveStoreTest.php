<?php

namespace Tests\Feature\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\FinanceReportCreator;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\ProxyLead\Models\PlApprovedReport;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Support\Status\Status;
use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApproveStoreTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;
    /** @var Carbon */
    protected $period;
    /** @var ProxyLeadSetting */
    protected $proxyLeadSetting;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->company = Company::factory()->create();
        $this->proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        $this->period = now()->startOfMonth();
    }

    /** @test */
    public function quest_can_not_add_approvement() :void
    {
        $this->withExceptionHandling()->makeRequest(['for_date' => $this->period->toDateString()])->assertStatus(401);
    }

    /**
     * @test
     */
    public function it_should_add_approvement() :void
    {
        $count = PlApprovedReport::getQuery()->count();
        $this->signInAsSuperAdmin()->makeRequest(['for_date' => $this->period->toDateString()])->assertStatus(201);
        $this->assertSame($count + 1, PlApprovedReport::getQuery()->count());
    }

    /**
     * @dataProvider forDateDataProvider
     * @test
     * @param $forDate
     * @param $status
     */
    public function for_date_must_be_required_and_valid($forDate, $status) :void
    {
        $this->signInAsSuperAdmin()
            ->withExceptionHandling()
            ->makeRequest(['for_date' => $forDate])
            ->assertStatus($status);
    }

    public function forDateDataProvider(): array
    {
        return [
            [null, 422],
            ['invalid', 422],
            ['', 422],
            ['2018-04-01', 201],
        ];
    }

    /** @test */
    public function proxy_lead_configuration_is_required(): void
    {
        $this->truncate(ProxyLeadSetting::class);
        $this->assertSame(0, ProxyLeadSetting::getQuery()->count());

        $this->signInAsSuperAdmin()
            ->withExceptionHandling()
            ->makeRequest(['for_date' => $this->period->toDateString()])
            ->assertStatus(422);
    }

    /** @test */
    public function if_approve_lead_is_already_exists_it_will_not_create_new_one() :void
    {
        $this->truncate(PlApprovedReport::class, false);

        $this->proxyLeadSetting->approvedReports()->create(['for_date' => $this->period->toDateString()]);

        $this->assertSame(1, PlApprovedReport::getQuery()->count());

        $this->signInAsSuperAdmin()
            ->withExceptionHandling()
            ->makeRequest(['for_date' => $this->period->toDateString()]);

        $this->assertSame(1, PlApprovedReport::getQuery()->count());
    }

    /**
     * @test
     */
    public function it_will_return_created_approve_report() :void
    {
        $response = $this->signInAsSuperAdmin()
            ->withExceptionHandling()
            ->makeRequest(['for_date' => $this->period->toDateString()])
            ->json();

        $this->assertSame($this->proxyLeadSetting->id, $response['proxy_lead_setting_id']);
        $this->assertSame($this->period->toDateString(), $response['for_date']);
    }

    /** @test */
    public function it_will_update_finance_report_status() :void
    {
        $this->truncate(FinanceReport::class, false);

        (new FinanceReportCreator($this->company, $this->period))->create();

        $this->assertSame(Status::COMPANY_RECONCILING, FinanceReport::first()->status);

        $this->signInAsSuperAdmin()
            ->withExceptionHandling()
            ->makeRequest(['for_date' => $this->period->toDateString()]);

        $this->assertSame(Status::NO_ORDERS, FinanceReport::first()->status);
    }

    private function makeRequest($data = []): TestResponse
    {
        return $this->postJson(
            route('account.company.proxy-lead.report.approvenew', [
                    'accountId' => $this->company->account_id,
                    'company' => $this->company,
                ]),
            $data
        );
    }
}
