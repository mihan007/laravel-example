<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\RoistatTargetCounter;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoistatTargetCounterTest extends TestCase
{
    use RefreshDatabase;

    /** @var Company */
    protected $company;

    /** @var RoistatCompanyConfig */
    protected $roistatConfig;

    /** @var RoistatTargetCounter */
    protected $counter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company]);
        $this->counter = new RoistatTargetCounter();
    }

    /** @test */
    public function leads_without_report_leads_are_all_targeted()
    {
        RoistatProxyLead::factory()->count(2)->create(['company_id' => $this->company]);
        $leads = RoistatProxyLead::with('reportLead')->get();

        $this->assertSame($this->counter->getTargetCount($leads), $leads->count());
        $this->assertSame($this->counter->getNotConfirmedCount($leads), 0);
        $this->assertSame($this->counter->getNonTargetCount($leads), 0);
    }

    /** @test */
    public function lead_should_be_targeted_if_user_confirm_them()
    {
        RoistatProxyLead::factory()->count(2)->create(['company_id' => $this->company])
            ->each(function (RoistatProxyLead $lead) {
                $lead->reportLead()->create(
                    array_merge($lead->toArray(), ['roistat_company_config_id' => $this->roistatConfig->id])
                );
            });
        $leads = RoistatProxyLead::with('reportLead')->get();

        $this->assertSame($this->counter->getTargetCount($leads), $leads->count());
        $this->assertSame($this->counter->getNotConfirmedCount($leads), 0);
        $this->assertSame($this->counter->getNonTargetCount($leads), 0);
    }

    /** @test */
    public function lead_should_be_missed_if_user_disagree_and_admin_agree()
    {
        RoistatProxyLead::factory()->count(2)->create(['company_id' => $this->company])
            ->each(function (RoistatProxyLead $lead) {
                $lead->reportLead()->update([
                        'roistat_company_config_id' => $this->roistatConfig->id,
                        'admin_confirmed' => 3,
                        'user_confirmed' => 0,
                    ]);
            });

        $leads = RoistatProxyLead::with('reportLead')->get();

        $this->assertSame($this->counter->getTargetCount($leads), 0);
        $this->assertSame($this->counter->getNotConfirmedCount($leads), 0);
        $this->assertSame($this->counter->getNonTargetCount($leads), $leads->count());
    }

    /** @test */
    public function lead_should_be_not_confirmed_if_user_disagree_and_admin_not_confirmed()
    {
        RoistatProxyLead::factory()->count(2)->create(['company_id' => $this->company])
            ->each(function (RoistatProxyLead $lead) {
                $lead->reportLead()->update([
                    'roistat_company_config_id' => $this->roistatConfig->id,
                    'admin_confirmed' => random_int(1, 2),
                    'user_confirmed' => 0,
                ]);
            });
        $leads = RoistatProxyLead::with('reportLead')->get();

        $this->assertSame($this->counter->getTargetCount($leads), 0);
        $this->assertSame($this->counter->getNotConfirmedCount($leads), $leads->count());
        $this->assertSame($this->counter->getNonTargetCount($leads), 0);
    }
}
