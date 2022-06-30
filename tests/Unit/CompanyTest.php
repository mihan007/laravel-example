<?php

namespace Tests\Unit;

use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RcBalanceTransaction;
use App\Domain\Roistat\Models\RoistatAnalytic;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use App\Domain\YandexDirect\Models\YandexDirectCompanyConfig;
use Tests\TestCase;

/**
 * Class CompanyTest.
 */
class CompanyTest extends TestCase
{
    /** @var \App\Domain\Channel\Models\Channel */
    protected $channel;
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Company::class);
        $this->truncate(Channel::class);
        $this->truncate(ProxyLeadSetting::class);
        $this->truncate(RoistatCompanyConfig::class);
        $this->channel = Channel::factory()->create();
        $this->company = Company::factory()->create(['channel_id' => $this->channel->id]);
    }

    /** @test */
    public function if_yandex_configuration_is_not_set_should_see_not_configured_status()
    {
        $this->assertSame(Company::YANDEX_IS_NOT_CONFIGURED, $this->company->yandex_status);
    }

    /** @test */
    public function if_yandex_configuration_auth_key_is_not_set_should_return_not_configured_status()
    {
        $this->company->each(function ($company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['yandex_auth_key' => '']);

            /* @var \App\Domain\Company\Models\Company $company */
            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->company->loadMissing('yandexDirectConfig');

        $this->assertNotNull($this->company->yandexDirectConfig);

        $this->assertSame(Company::YANDEX_IS_NOT_CONFIGURED, $this->company->yandex_status);
    }

    /** @test */
    public function if_yandex_configuration_login_is_not_set_should_return_not_configured_status()
    {
        $this->company->each(function ($company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['yandex_login' => '']);

            /* @var \App\Domain\Company\Models\Company $company */
            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->company->loadMissing('yandexDirectConfig');

        $this->assertNotNull($this->company->yandexDirectConfig);

        $this->assertSame(Company::YANDEX_IS_NOT_CONFIGURED, $this->company->yandex_status);
    }

    /** @test */
    public function if_yandex_configuration_is_set_but_there_is_no_set_any_balance_should_return_error_status()
    {
        $this->company->each(function ($company) {
            /* @var Company $company */
            $company->yandexDirectConfig()->create(YandexDirectCompanyConfig::factory()->make()->toArray());
        });

        $this->company->loadMissing('yandexDirectConfig');

        $this->assertNotNull($this->company->yandexDirectConfig);

        $this->assertSame(Company::YANDEX_HAS_ERRORS, $this->company->yandex_status);
    }

    /** @test */
    public function if_yandex_configuration_is_set_and_balance_less_limit_should_return_error_status()
    {
        $this->company->each(function ($company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['limit_amount' => 200]);

            /* @var \App\Domain\Company\Models\Company $company */
            $company->yandexBalances()->create(YandexDirectBalance::factory()->make(['amount' => 100])->toArray());

            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->company->loadMissing(['yandexDirectConfig', 'yandexLatestBalace']);

        $this->assertNotNull($this->company->yandexDirectConfig);
        $this->assertNotNull($this->company->yandexLatestBalace);

        $this->assertSame(Company::YANDEX_HAS_ERRORS, $this->company->yandex_status);
    }

    /** @test */
    public function it_should_show_error_status_for_yandex_configurtion_if_limit_is_zero_and_amount_is_zero(): void
    {
        $this->company->each(function (Company $company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['limit_amount' => 0]);

            $company->yandexBalances()->create(YandexDirectBalance::factory()->make(['amount' => 0])->toArray());

            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->company->loadMissing(['yandexDirectConfig', 'yandexLatestBalace']);

        $this->assertSame(Company::YANDEX_HAS_ERRORS, $this->company->yandex_status);
    }

    /** @test */
    public function if_there_were_not_yandex_balance_for_today_it_should_return_error_status(): void
    {
        $this->company->each(function (Company $company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['limit_amount' => 200]);

            create(
                YandexDirectBalance::class,
                ['company_id' => $company->id, 'amount' => 300, 'created_at' => now()->subDay()->toDateString()]
            );

            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->assertSame(Company::YANDEX_HAS_ERRORS, $this->company->yandex_status);
    }

    /** @test */
    public function if_yandex_works_fine_should_return_successful_status()
    {
        $this->company->each(function ($company) {
            $yandexConfig = YandexDirectCompanyConfig::factory()->make(['limit_amount' => 200]);

            /* @var \App\Domain\Company\Models\Company $company */
            $company->yandexBalances()->create(YandexDirectBalance::factory()->make(['amount' => 300])->toArray());

            $company->yandexDirectConfig()->create($yandexConfig->toArray());
        });

        $this->company->loadMissing(['yandexDirectConfig', 'yandexLatestBalace']);

        $this->assertNotNull($this->company->yandexDirectConfig);
        $this->assertNotNull($this->company->yandexLatestBalace);

        $this->assertSame(Company::YANDEX_OK, $this->company->yandex_status);
    }

    /** @test */
    public function if_roistat_balance_configuration_is_not_set_should_return_not_configured_status()
    {
        $this->assertSame(Company::ROISTAT_IS_NOT_OONFIGURED, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_company_config_set_without_rc_balance_config_should_return_not_configured_status()
    {
        $this->company->each(function (Company $company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        });

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertSame(Company::ROISTAT_IS_NOT_OONFIGURED, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_company_config_does_not_have_project_id_it_should_return_not_configured_status(): void
    {
        $this->company->each(function (Company $company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id, 'roistat_project_id' => '']);
            RcBalanceConfig::factory()->create(['company_id' => $company->id]);
        });

        $this->assertSame(Company::ROISTAT_IS_NOT_OONFIGURED, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_company_config_does_not_have_api_key_it_should_return_not_configured_status()
    {
        $this->company->each(function ($company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id, 'api_key' => '']);
            RcBalanceConfig::factory()->create(['company_id' => $company->id]);
        });

        $this->assertSame(Company::ROISTAT_IS_NOT_OONFIGURED, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_balance_configuration_does_not_have_any_transactions_should_return_error()
    {
        $this->company->each(function (Company $company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
            RcBalanceConfig::factory()->create(['company_id' => $company->id]);
        });

        $this->assertSame(Company::ROISTAT_HAS_ERRORS, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_balance_less_then_limit_should_return_error_status()
    {
        $this->company->each(function ($company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
            $balanceConfig = RcBalanceConfig::factory()->create(['company_id' => $company->id, 'limit_amount' => 200]);

            $balanceConfig->latestTransaction()->create(
                RcBalanceTransaction::factory()->make(['balance' => 100])->toArray()
            );
        });

        $this->assertSame(Company::ROISTAT_HAS_ERRORS, $this->company->roistat_status);
    }

    /** @test */
    public function if_roistat_balance_works_fine_should_return_successful_status()
    {
        $this->company->each(function ($company) {
            RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
            $balanceConfig = RcBalanceConfig::factory()->create(['company_id' => $company->id, 'limit_amount' => 300]);

            $balanceConfig->latestTransaction()->create(
                RcBalanceTransaction::factory()->make(['balance' => 400])->toArray()
            );
        });

        $this->assertSame(Company::ROISTAT_OK, $this->company->roistat_status);
    }

    /** @test */
    public function google_status_should_be_not_configured_if_roistat_company_config_is_not_set()
    {
        $this->assertSame(Company::GOOGLE_NOT_CONFIGURED, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_be_not_configured_if_roistat_company_config_roistat_project_id_does_not_set()
    {
        $this->company->roistatConfig()->create(
            RoistatCompanyConfig::factory()->make(['roistat_project_id' => ''])->toArray()
        );

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertSame(Company::GOOGLE_NOT_CONFIGURED, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_be_not_configured_if_roistat_company_config_api_key_does_not_set()
    {
        $this->company->roistatConfig()->create(
            RoistatCompanyConfig::factory()->make(['api_key' => ''])->toArray()
        );

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertSame(Company::GOOGLE_NOT_CONFIGURED, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_be_not_configured_if_google_limit_is_zero(): void
    {
        $this->company->roistatConfig()->create(
            RoistatCompanyConfig::factory()->make(['google_limit_amount' => 0])->toArray()
        );

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertSame(Company::GOOGLE_NOT_CONFIGURED, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_has_errors_if_roistat_company_config_does_not_have_most_recent_analytics()
    {
        $this->company->roistatConfig()->create(RoistatCompanyConfig::factory()->make()->toArray());

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertSame(Company::GOOGLE_HAS_ERRORS, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_has_errors_if_there_is_no_most_recent_google_analytic()
    {
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $config */
        $config = $this->company->roistatConfig()->create(RoistatCompanyConfig::factory()->make()->toArray());
        $config->analytics()->create(
            RoistatAnalytic::factory()->make(['for_date' => now()->subDay()->toDateString()])->toArray()
        );

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertTrue($config->mostRecentAnalytic()->exists());
        $this->assertSame(Company::GOOGLE_HAS_ERRORS, $this->company->google_status);
    }

    /** @test */
    public function google_status_should_has_errors_if_visit_cost_less_then_google_limit_amount()
    {
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $config */
        $config = $this->company->roistatConfig()->create(
            RoistatCompanyConfig::factory()->make(['google_limit_amount' => 10])->toArray()
        );
        $config->analytics()->create(
            RoistatAnalytic::factory()->make(['for_date' => now()->subDay()->toDateString(), 'visitsCost' => 5])->toArray()
        );
        $config->googleAnalytics()->create(
            RoistatAnalytic::factory()->make(['for_date' => now()->subDay()->toDateString(), 'visitsCost' => 5])->toArray()
        );

        $this->assertTrue($this->company->roistatConfig()->exists());
        $this->assertTrue($config->mostRecentAnalytic()->exists());
        $this->assertTrue($config->mostRecentGoogleAnalytic()->exists());
        $this->assertSame(Company::GOOGLE_HAS_ERRORS, $this->company->google_status);
    }

    /** @test */
    public function it_can_have_many_goal_counters()
    {
        $this->truncate(ProxyLeadGoalCounter::class, false);

        $company = Company::factory()->create();
        ProxyLeadGoalCounter::factory()->count(2)->create(['company_id' => $company->id, 'lead_cost' => 0]);

        $this->assertSame(2, $company->proxyLeadGoalCounters()->count());
    }

    /** @test */
    public function it_has_many_finance_reports() :void
    {
        $company = Company::factory()->create();

        $this->assertSame(0, $company->financeReports()->count());

        FinanceReport::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertSame(2, $company->financeReports()->count());
    }

    /** @test */
    public function it_should_take_latest_yandex_balance() :void
    {
        $company = Company::factory()->create();
        $yandexBalances1 = YandexDirectBalance::factory()->create(['company_id' => $company->id]);
        sleep(1);
        $yandexBalances2 = YandexDirectBalance::factory()->create(['company_id' => $company->id]);

        $this->assertSame(1, $company->yandexLatestBalace()->get()->count());
        $this->assertSame($yandexBalances2->id, $company->yandexLatestBalace()->first()->id);
    }

    /**
     * @test
     */
    public function create_company_will_add_public_key(): void
    {
        $company = Company::create(['name' => 'Some name', 'channel_id' => $this->channel->id]);

        $this->assertNotNull($company->public_id);
    }

    /** @test */
    public function it_can_have_channel(): void
    {
        $channel = Channel::factory()->create();
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create(['channel_id' => $channel->id]);

        $this->assertSame(1, $company->channel()->count());
    }

    /** @test */
    public function it_will_return_roistat_configuration_if_proxy_lead_settings_is_not_set(): void
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(RoistatCompanyConfig::class, $company->getProxyLeadConfig());
    }

    /** @test */
    public function it_will_return_proxy_lead_configuration_if_proxy_lead_setting_is_set(): void
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();
        RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        ProxyLeadSetting::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(ProxyLeadSetting::class, $company->getProxyLeadConfig());
    }

    /** @test */
    public function it_will_return_null_if_company_does_not_have_any_proxy_lead_configuration(): void
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::factory()->create();

        $this->assertNull($company->getProxyLeadConfig());
    }
}
