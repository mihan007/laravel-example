<?php

namespace Tests\Unit;

use App\Domain\Finance\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    /** @test */
    public function it_has_finance_report() :void
    {
        $payment = Payment::factory()->create();

        $this->assertSame(1, $payment->financeReport()->count());
    }
}
