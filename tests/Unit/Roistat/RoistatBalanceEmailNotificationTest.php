<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RcBalanceTransaction;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\RoistatBalanceEmailNotification;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RoistatBalanceEmailNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function it_should_skip_company_if_its_balance_more_then_limit(): void
    {
        $company = Company::factory()->create();
        $balanceConfig = RcBalanceConfig::factory()->create(['company_id' => $company->id, 'limit_amount' => 100]);
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);

        $transaction = RcBalanceTransaction::factory()->create(['balance' => 200, 'rc_balance_config_id' => $balanceConfig->id]);

        (new RoistatBalanceEmailNotification)->check();

        Mail::assertNothingSent();
    }
}
