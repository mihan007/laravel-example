<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\RoistatGoogleNotification;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Roistat\GoogleAmountEmailNotification;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GoogleAmountEmailNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function it_should_not_send_email_when_there_is_no_google_analytic(): void
    {
        $company = Company::factory()->create();
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $company->id, 'google_limit_amount' => 10]);
        $recipient = EmailNotification::factory()->create(['company_id' => $company->id, 'type' => 'roistat_google']);

        (new GoogleAmountEmailNotification())->check();

        Mail::assertSent(RoistatGoogleNotification::class, 0);
    }
}
