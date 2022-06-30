<?php

namespace Tests\Unit;

use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\Payment;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    /** @test */
    public function it_has_many_payments() :void
    {
        $report = FinanceReport::factory()->create();

        $this->assertSame(0, $report->payments()->count());

        Payment::factory()->count(2)->create(['finance_report_id' => $report->id]);

        $this->assertSame(2, $report->payments()->count());
    }

    /** @test */
    public function it_belongs_to_company() :void
    {
        $report = FinanceReport::factory()->create();

        $this->assertSame(1, $report->company()->count());
    }
}
