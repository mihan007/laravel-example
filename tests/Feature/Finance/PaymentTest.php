<?php

namespace Tests\Feature\Company\Feature;

use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Payment::class);
        $this->truncate(FinanceReport::class);
    }

    /** @test */
    public function guest_can_not_add_payment() :void
    {
        $this->withExceptionHandling()->storePaymentRequest()->assertStatus(302);
    }

    /** @test */
    public function it_should_store_payment() :void
    {
        $report = FinanceReport::factory()->create();

        $this->assertSame(0, Payment::getQuery()->count());

        $this->signInAsSuperAdmin()->withExceptionHandling()
            ->storePaymentRequest(['finance_report_id' => $report->id, 'amount' => 200])
            ->assertStatus(201);

        $this->assertSame(1, Payment::getQuery()->count());
    }

    /** @test */
    public function finance_report_should_be_valid() :void
    {
        $this->signInAsSuperAdmin()->withExceptionHandling();

        $this->storePaymentRequest(['finance_report_id' => 999])
            ->assertStatus(302)
            ->assertSessionHasErrors('finance_report_id');
    }

    /** @test */
    public function amount_should_be_valid() :void
    {
        $this->signInAsSuperAdmin()->withExceptionHandling();

        $report = FinanceReport::factory()->create();

        $this->storePaymentRequest(['finance_report_id' => $report->id, 'amount' => 'invalid'])
            ->assertStatus(302)
            ->assertSessionHasErrors('amount');
    }

    /** @test */
    public function quest_can_not_delete_payment() :void
    {
        $payment = Payment::factory()->create()->id;

        $this->withExceptionHandling()->deletePaymentRequest(['payment' => $payment])->assertStatus(302);
    }

    /** @test */
    public function user_can_delete_payments() :void
    {
        $payment = Payment::factory()->create()->id;

        $this->assertSame(1, Payment::getQuery()->count());

        $this->signInAsSuperAdmin()->deletePaymentRequest(['payment' => $payment]);

        $this->assertSame(0, Payment::getQuery()->count());
    }

    /** @test */
    public function after_addition_payment_it_will_recalculate_attach_finance_report() :void
    {
        $report = FinanceReport::factory()->create(['paid' => 0]);

        $this->assertSame((float) 0, $report->paid);

        $this->signInAsSuperAdmin()->storePaymentRequest(['finance_report_id' => $report->id, 'amount' => 200]);

        $this->assertSame((float) 200, $report->fresh()->paid);
    }

    private function storePaymentRequest($params = [])
    {
        return $this->post(route('finance.payment.store', $params));
    }

    private function deletePaymentRequest($params = [])
    {
        return $this->delete(route('finance.payment.destroy', $params));
    }
}
