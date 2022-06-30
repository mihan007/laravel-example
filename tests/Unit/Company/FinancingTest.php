<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\Payment;
use App\Domain\ProxyLead\Models\PlApprovedReport;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\Models\Reconclication;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatReconciliation;
use App\Models\ApprovedReport;
use App\Support\Status\Status;
use Carbon\Carbon;
use Tests\TestCase;

class FinancingTest extends TestCase
{
    /** @var Carbon */
    protected $period;
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->period = now()->subMonth()->startOfMonth();
        $this->company = Company::factory()->create();
    }

    /** @test */
    public function it_must_return_not_configured_status_if_company_does_not_have_any_proxy_lead_configuration() :void
    {
        $this->assertSame(Status::NOT_CONFIGURED, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_proxy_lead_settings_and_no_reconclications() :void
    {
        ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);

        $this->assertSame(Status::COMPANY_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_proxy_lead_settings_and_last_user_reconcilication() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);

        create(
            Reconclication::class,
            [
                'proxy_lead_setting_id' => $proxyLeadSetting->id,
                'period' => $this->period->toDateString(),
                'type' => Reconclication::USER_TYPE,
            ]
        );

        $this->assertSame(Status::COMPANY_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_proxy_lead_settings_and_last_admin_reconcilication() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);

        create(
            Reconclication::class,
            [
                'proxy_lead_setting_id' => $proxyLeadSetting->id,
                'period' => $this->period->toDateString(),
                'type' => Reconclication::ADMIN_TYPE,
            ]
        );

        $this->assertSame(Status::USER_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_waiting_for_payment_status_if_company_has_proxy_lead_setting_and_report_is_confirmed() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        create(
            PlApprovedReport::class,
            ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 1, 'for_date' => $this->period->toDateString()]
        );

        $this->assertSame(Status::WAITING_FOR_PAYMENT, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_no_orders_if_company_has_proxy_lead_settings_and_approved_and_no_proxy_lead_goal_counters() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        create(
            PlApprovedReport::class,
            ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $this->period->toDateString()]
        );

        // zero records in ProxyLeadGoalCounter
        $this->assertSame(Status::NO_ORDERS, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_no_orders_if_company_has_proxy_lead_settings_and_approved_and_zero_target_leads() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        create(
            PlApprovedReport::class,
            ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 0, 'for_date' => $this->period->toDateString()]
        );

        // zero records in ProxyLeadGoalCounter
        $this->assertSame(Status::NO_ORDERS, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_partially_paid_if_company_has_proxy_lead_settings_and_approved_with_positive_target_leads_and_payments() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        create(
            PlApprovedReport::class,
            ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $this->period->toDateString()]
        );
        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 10, 'for_date' => $this->period->toDateString()]
        );

        $financeReport = create(
            FinanceReport::class,
            ['company_id' => $this->company->id, 'for_date' => $this->period->toDateString()]
        );

        $financeReport->payments()->create(['amount' => 200]);

        $this->assertSame(Status::PARTIALLY_PAID, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_fully_paid_if_company_has_proxy_lead_settings_and_arpoved_with_leads_and_fully_paid() :void
    {
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        create(
            PlApprovedReport::class,
            ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $this->period->toDateString()]
        );
        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 10, 'for_date' => $this->period->toDateString()]
        );

        $financeReport = create(
            FinanceReport::class,
            ['company_id' => $this->company->id, 'to_pay' => 400, 'for_date' => $this->period->toDateString()]
        );

        $financeReport->payments()->create(['amount' => 400]);

        $this->assertSame(Status::FULLY_PAID, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_roistat_company_configs_and_no_reconciliations() :void
    {
        RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);

        $this->assertSame(Status::COMPANY_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_roistat_company_configs_and_last_user_reconcilication() :void
    {
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);

        create(
            RoistatReconciliation::class,
            [
                'roistat_company_config_id' => $config->id,
                'period' => $this->period->toDateString(),
                'type' => RoistatReconciliation::USER_TYPE,
            ]
        );

        $this->assertSame(Status::COMPANY_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_company_reconciling_status_if_company_has_roistat_company_config_and_last_admin_reconcilication() :void
    {
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);

        create(
            RoistatReconciliation::class,
            [
                'roistat_company_config_id' => $config->id,
                'period' => $this->period->toDateString(),
                'type' => RoistatReconciliation::ADMIN_TYPE,
            ]
        );

        $this->assertSame(Status::USER_RECONCILING, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_waiting_for_payment_status_if_company_has_roistat_company_config_and_report_is_confirmed() :void
    {
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
        create(
            ApprovedReport::class,
            ['roistat_company_config_id' => $roistatConfig->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 1, 'for_date' => $this->period->toDateString()]
        );

        $this->assertSame(Status::WAITING_FOR_PAYMENT, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_no_orders_if_company_has_roistat_company_config_and_approved_and_no_proxy_lead_goal_counters() :void
    {
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
        create(
            ApprovedReport::class,
            ['roistat_company_config_id' => $roistatConfig->id, 'for_date' => $this->period->toDateString()]
        );

        // zero records in ProxyLeadGoalCounter
        $this->assertSame(Status::NO_ORDERS, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_no_orders_if_company_has_roistat_company_config_and_approved_and_zero_target_leads() :void
    {
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
        create(
            ApprovedReport::class,
            ['roistat_company_config_id' => $roistatConfig->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 0, 'for_date' => $this->period->toDateString()]
        );

        // zero records in ProxyLeadGoalCounter
        $this->assertSame(Status::NO_ORDERS, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_partially_paid_if_company_has_roistat_company_config_and_aproved_with_leads_and_paids() :void
    {
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
        create(
            ApprovedReport::class,
            ['roistat_company_config_id' => $roistatConfig->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 10, 'for_date' => $this->period->toDateString()]
        );

        $financeReport = create(
            FinanceReport::class,
            ['company_id' => $this->company->id, 'to_pay' => 400, 'for_date' => $this->period->toDateString()]
        );

        Payment::factory()->create(['finance_report_id' => $financeReport->id, 'amount' => 200]);

        $this->assertSame(Status::PARTIALLY_PAID, $this->company->getFinanceStatus($this->period));
    }

    /** @test */
    public function it_must_return_fully_paid_if_company_has_roistat_company_config_and_approved_with_leads_and_fully_paid() :void
    {
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $this->company->id]);
        create(
            ApprovedReport::class,
            ['roistat_company_config_id' => $roistatConfig->id, 'for_date' => $this->period->toDateString()]
        );

        create(
            ProxyLeadGoalCounter::class,
            ['company_id' => $this->company->id, 'target' => 10, 'for_date' => $this->period->toDateString()]
        );

        $financeReport = create(
            FinanceReport::class,
            ['company_id' => $this->company->id, 'to_pay' => 400, 'for_date' => $this->period->toDateString()]
        );

        Payment::factory()->create(['finance_report_id' => $financeReport->id, 'amount' => 400]);

        $this->assertSame(Status::FULLY_PAID, $this->company->getFinanceStatus($this->period));
    }
}
