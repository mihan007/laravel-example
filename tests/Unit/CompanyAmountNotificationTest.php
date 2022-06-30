<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\CustomerBalanceNotification;
use App\Domain\Notification\Mail\RoistatBalanceNotification;
use App\Domain\Notification\Mail\RoistatGoogleNotification;
use App\Domain\Notification\Mail\YandexDirectNotification;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\Roistat\GoogleAmountEmailNotification;
use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RcBalanceTransaction;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatGoogleAnalytic;
use App\Domain\Roistat\RoistatBalanceEmailNotification;
use App\Domain\YandexDirect\MailNotifications;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use App\Domain\YandexDirect\Models\YandexDirectCompanyConfig;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Class CompanyAmountNotificationTest.
 */
class CompanyAmountNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Queue::fake();
        Mail::fake();
    }

    /** @test */
    public function it_should_not_send_email_when_google_limit_is_not_lower_than_permissible()
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(EmailNotification::class, false);
        $this->truncate(RcBalanceConfig::class, false);
        $company = Company::factory()->create();
        $roistatCompanyConfig = RoistatCompanyConfig::factory()->create([
            'company_id' => $company->id,
            'google_limit_amount' => 1000,
        ]);
        $emailNotification = EmailNotification::factory()->create([
            'company_id' => $company->id,
            'type' => 'roistat_google',
        ]);
        $rcBalanceConfig = RcBalanceConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 20,
        ]);

        (new RoistatBalanceEmailNotification)->check();
        Mail::assertSent(RoistatGoogleNotification::class, 0);
    }

    /** @test */
    public function it_should_not_send_email_when_roistat_limit_is_not_lower_than_permissible(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(RcBalanceConfig::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RcBalanceTransaction::class, false);
        $this->truncate(EmailNotificationSetting::class, false);
        $company = Company::factory()->create();
        $balanceConfig = RcBalanceConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 1000,
        ]);
        $roistatConfig = RoistatCompanyConfig::factory()->create([
            'company_id' => $company->id,
        ]);
        $emailNotificationSetting = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=>'roistat_balance',
        ]);
        $rcBalanceTransaction = RcBalanceTransaction::factory()->create([
            'balance' => 1500,
            'rc_balance_config_id' => $balanceConfig->id,
        ]);

        (new RoistatBalanceEmailNotification)->check();

        Mail::assertSent(RoistatBalanceNotification::class, 0);
    }

    /** @test */
    public function it_should_not_send_email_when_yandex_limit_is_not_lower_than_permissible(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(YandexDirectBalance::class, false);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(EmailNotificationSetting::class, false);

        $company = Company::factory()->create();
        $yandex_direct_Balance = YandexDirectBalance::factory()->create([
            'company_id' => $company->id,
            'amount' => 1500,
        ]);
        $yandex_direct_company_config = YandexDirectCompanyConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 1000,
            'amount' => 1500,
        ]);
        $email_no = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=>'yandex_direct',
        ]);
        (new MailNotifications)->check();

        Mail::assertSent(YandexDirectNotification::class, 0);
    }

    /** @test */
    public function it_should_not_send_email_when_lidogenerator_limit_is_not_lower_than_permissible(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(EmailNotificationSetting::class, false);

        $company = Company::factory()->create([
            'balance' => 1000,
            'balance_limit' => 500,
        ]);

        $emailNotificationSetting = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=> EmailNotification::CUSTOMER_BALANCE,
        ]);

        new BalanceNotifier($company);

        Mail::assertSent(YandexDirectNotification::class, 0);
    }

    /**
     * @test
     */
    public function send_email_if_the_google_limit_is_not_lower_than_acceptable()
    {
        $this->truncate(Company::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(EmailNotification::class, false);
        $this->truncate(RcBalanceConfig::class, false);
        $this->truncate(RoistatGoogleAnalytic::class, false);
        $this->truncate(RcBalanceTransaction::class, false);
        $company = Company::factory()->create();
        $roistatCompanyConfig = RoistatCompanyConfig::factory()->create([
            'company_id' => $company->id,
            'google_limit_amount' => 1000,
        ]);
        $roistatGoogleAnalytic = RoistatGoogleAnalytic::factory()->create([
            'roistat_company_config_id' => $roistatCompanyConfig->id,
            'visitsCost' => 100,
        ]);
        $emailNotification = EmailNotification::factory()->create([
            'company_id' => $company->id,
            'type' => 'roistat_google',
        ]);
        $emailNotificationSetting = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=>'roistat_google',
        ]);
        $rcBalanceConfig = RcBalanceConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 20,
        ]);
        $rcBalanceTransaction = RcBalanceTransaction::factory()->create([
            'balance' => 10,
            'rc_balance_config_id' => $rcBalanceConfig->id,
        ]);

        (new GoogleAmountEmailNotification())->check();

        Mail::assertQueued(RoistatGoogleNotification::class);
    }

    /** @test */
    public function send_email_if_the_roistat_limit_is_not_lower_than_acceptable(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(RcBalanceConfig::class, false);
        $this->truncate(RoistatCompanyConfig::class, false);
        $this->truncate(RcBalanceTransaction::class, false);
        $this->truncate(EmailNotification::class, false);
        $company = Company::factory()->create();
        $balanceConfig = RcBalanceConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 100,
        ]);
        $roistatConfig = RoistatCompanyConfig::factory()->create(['company_id' => $company->id]);
        $emailNotificationSetting = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=>'roistat_balance',
        ]);
        $rcBalanceTransaction = RcBalanceTransaction::factory()->create([
            'balance' => 50,
            'rc_balance_config_id' => $balanceConfig->id,
        ]);

        (new RoistatBalanceEmailNotification)->check();

        Mail::assertQueued(RoistatBalanceNotification::class);
    }

    /** @test */
    public function send_email_if_the_yandex_limit_is_not_lower_than_acceptable(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(YandexDirectBalance::class, false);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(EmailNotification::class, false);

        $company = Company::factory()->create();
        $yandex_direct_Balance = YandexDirectBalance::factory()->create([
            'company_id' => $company->id,
            'amount' => 100,
        ]);
        $yandex_direct_company_config = YandexDirectCompanyConfig::factory()->create([
            'company_id' => $company->id,
            'limit_amount' => 100,
            'amount' => 50,
        ]);
        $email_no = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=>'yandex_direct',
        ]);

        (new MailNotifications)->check();

        Mail::assertQueued(YandexDirectNotification::class);
    }

    /** @test */
    public function send_email_if_the_lidogenerator_limit_is_not_lower_than_acceptable(): void
    {
        $this->truncate(Company::class, false);
        $this->truncate(EmailNotificationSetting::class, false);

        $company = Company::factory()->create([
            'prepayment' => 1,
            'balance' => 50,
            'balance_limit' => 100,
        ]);

        $emailNotificationSetting = EmailNotificationSetting::factory()->create([
            'company_id' => $company->id,
            'notification_type'=> 'customer_balance',
        ]);

        new BalanceNotifier($company);

        \Mail::assertQueued(CustomerBalanceNotification::class);
    }
}
