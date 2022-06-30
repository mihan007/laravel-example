<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use Artisan;
use Tests\TestCase;

class GenerateFinanceReportsTest extends TestCase
{
    /** @test */
    public function it_should_create_reports_for_all_companies() :void
    {
        $this->truncate(Company::class);
        $this->truncate(FinanceReport::class);

        Company::factory()->count(2)->create();

        Artisan::call('finance:generate');

        $this->assertSame(2, FinanceReport::getQuery()->count());
    }

    /** @test */
    public function by_default_it_will_create_reports_for_previous_month() :void
    {
        $this->truncate(Company::class);

        Company::factory()->create();

        Artisan::call('finance:generate');

        $this->assertSame(now()->startOfMonth()->subMonth()->toDateString(), FinanceReport::first()->for_date);
    }

    /** @test */
    public function we_can_manage_period() :void
    {
        $this->truncate(Company::class);
        $this->truncate(FinanceReport::class, false);

        Company::factory()->create();
        $period = now()->startOfMonth()->subMonth(3);

        Artisan::call('finance:generate', ['--period' => $period->toDateString()]);

        $this->assertSame($period->toDateString(), FinanceReport::first()->for_date);
    }
}
