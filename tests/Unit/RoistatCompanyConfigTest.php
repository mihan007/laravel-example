<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatAnalytic;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatGoogleAnalytic;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatReconciliation;
use Tests\TestCase;

/**
 * Class RoistatCompanyConfigTest.
 */
class RoistatCompanyConfigTest extends TestCase
{
    /** @test */
    public function it_should_return_most_recent_analytic(): void
    {
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $config */
        $config = RoistatCompanyConfig::factory()->create();

        $yesterdayAnalytic = RoistatAnalytic::factory()->create(['roistat_company_config_id' => $config->id, 'for_date' => now()->subDay()->toDateString()]);
        $todayAnalytic = RoistatAnalytic::factory()->create(['roistat_company_config_id' => $config->id, 'for_date' => now()->toDateString()]);
        $tomorrowAnalytic = RoistatAnalytic::factory()->create(['roistat_company_config_id' => $config->id, 'for_date' => now()->addDay()->toDateString()]);

        $config->loadMissing('mostRecentAnalytic');

        $toArray = $yesterdayAnalytic->toArray();
        unset($toArray['roistat_company_config']);
        $this->assertEquals($toArray, $config->mostRecentAnalytic->toArray());
        $this->assertNotEquals($todayAnalytic->toArray(), $config->mostRecentAnalytic->toArray());
        $this->assertNotEquals($tomorrowAnalytic->toArray(), $config->mostRecentAnalytic->toArray());
    }

    /** @test */
    public function it_can_have_roistat_reconciliations() :void
    {
        $this->truncate(RoistatReconciliation::class, false);

        $config = RoistatCompanyConfig::factory()->create();
        RoistatReconciliation::factory()->count(2)->create(['roistat_company_config_id' => $config->id]);

        $this->assertSame(2, $config->roistatReconciliations()->count());
    }

    /** @test */
    public function it_should_take_only_latest_goolge_analytic() :void
    {
        $config = RoistatCompanyConfig::factory()->create();
        $analytic1 = RoistatGoogleAnalytic::factory()->create(['roistat_company_config_id' => $config->id]);
        sleep(1);
        $analytic2 = RoistatGoogleAnalytic::factory()->create(['roistat_company_config_id' => $config->id]);

        $this->assertSame(1, $config->mostRecentGoogleAnalytic()->get()->count());
        $this->assertEquals($analytic2->toArray(), $config->mostRecentGoogleAnalytic()->first()->toArray());
    }

    /** @test */
    public function it_should_take_only_latest_analytic() :void
    {
        $config = RoistatCompanyConfig::factory()->create();
        $analytic1 = RoistatAnalytic::factory()->create(['roistat_company_config_id' => $config->id, 'for_date' => now()->subDay()->toDateString()]);
        $analytic2 = RoistatAnalytic::factory()->create(['roistat_company_config_id' => $config->id, 'for_date' => now()->subDay(2)->toDateString()]);

        $this->assertSame(1, $config->mostRecentAnalytic()->get()->count());
        $this->assertSame($analytic1->id, $config->mostRecentAnalytic()->first()->id);
    }

    /**
     * @dataProvider timezoneDataProvider
     * @test
     */
    public function it_will_get_php_timezones_according_to_configured_timezone($timezone, $phpTimezone): void
    {
        $config = RoistatCompanyConfig::factory()->create(['timezone' => $timezone]);

        $this->assertSame($phpTimezone, $config->php_timezone);
    }

    public function timezoneDataProvider()
    {
        return [
            ['+0200', 'Europe/Kaliningrad'],
            ['+0300', 'Europe/Moscow'],
            ['+0400', 'Europe/Samara'],
            ['+0500', 'Asia/Yekaterinburg'],
            ['+0600', 'Asia/Omsk'],
            ['+0700', 'Asia/Krasnoyarsk'],
            ['+0800', 'Asia/Irkutsk'],
            ['+0900', 'Asia/Yakutsk'],
            ['+1000', 'Asia/Vladivostok'],
            ['+1100', 'Asia/Srednekolymsk'],
        ];
    }

    /** @test */
    public function it_will_get_attach_report_leads(): void
    {
        // set different id of company and roistat company configs
        $companyFirst = Company::factory()->create();

        $company = Company::factory()->create();
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $config */
        $config = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        RoistatProxyLead::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertSame(2, $config->leads()->count());
    }
}
