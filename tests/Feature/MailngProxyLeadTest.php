<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\AdminBalanceLimitCheck;
use App\Domain\Notification\Mail\CustomerBalanceNotification;
use App\Domain\Notification\Mail\ModerarionApplications;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Carbon\Carbon;
use Faker;
use Mail;
use Tests\TestCase;

/**
 * Class MailngProxyLeadTest.
 * @group MailngProxyLeadTest
 */
class MailngProxyLeadTest extends TestCase
{
    /** @var Faker\Generator */
    protected $company;

    /** @var \App\Domain\Company\Models\Company */
    protected $faker;

    /** @var ProxyLeadSetting */
    protected $proxyLeadSettings;

    /** @var \App\Domain\ProxyLead\Models\ProxyLead */
    protected $proxyLead;

    /** @var \App\Domain\Notification\Models\EmailNotificationSetting */
    protected $notificationSettingAdmin;

    /** @var \App\Domain\Notification\Models\EmailNotificationSetting */
    protected $notificationSettingManager;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->faker = Faker\Factory::create();

        $this->truncate(EmailNotificationSetting::class, false);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(Company::class, false);

        $this->company = Company::factory()->create();
        $this->proxyLeadSettings = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        $this->proxyLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSettings->id]);
        $this->notificationSettingAdmin = EmailNotificationSetting::factory()->create([
            'company_id' => $this->company->id,
            'notification_type' => EmailNotification::MAIN_TYPE,
            'status' => EmailNotificationSetting::STATUS_APPROVED,
        ]);
        $this->notificationSettingManager = EmailNotificationSetting::factory()->create([
            'company_id' => $this->company->id,
            'notification_type' => EmailNotification::PROXY_LEADS,
            'status' => EmailNotificationSetting::STATUS_APPROVED,
        ]);
    }

    /** @test */
    public function send_moderations()
    {
        $reportLead = $this->createReportLead();

        $reportLead->admin_confirmed = PlReportLead::STATUS_DISAGREE;
        $reportLead->admin_comment = $this->faker->text;
        $reportLead->moderation_status = PlReportLead::STATUS_AGREE;
        $reportLead->save();

        $check_send_moderation = PlReportLead::leftJoin('proxy_leads', 'pl_report_leads.proxy_lead_id', '=', 'proxy_leads.id')
            ->select('pl_report_leads.updated_at')
            ->where('proxy_leads.proxy_lead_setting_id', $this->proxyLeadSettings->id)
            ->where('pl_report_leads.admin_confirmed', 0)
            ->where('pl_report_leads.moderation_status', 1)
            ->orderBy('pl_report_leads.updated_at', 'DESC')
            ->first();

        $this->assertNotEmpty($check_send_moderation);
        $this->assertTrue($check_send_moderation->updated_at->gt(Carbon::now()->subMinutes(20)));

        $send_moderation = PlReportLead::leftJoin('proxy_leads', 'pl_report_leads.proxy_lead_id', '=', 'proxy_leads.id')
            ->select('proxy_leads.id')
            ->where('proxy_leads.proxy_lead_setting_id', $this->proxyLeadSettings->id)
            ->where('pl_report_leads.admin_confirmed', 0)
            ->where('pl_report_leads.moderation_status', 1)
            ->orderBy('pl_report_leads.updated_at', 'DESC')
            ->get();

        $this->assertCount(1, $send_moderation);

        $proxy_leads_ids = [];

        foreach ($send_moderation as $item) {
            $proxy_leads_ids[] = $item->id;
        }

        $recipients = $this->proxyLeadSettings->company->recipientsNotifications()->get();

        if ($recipients->isEmpty()) {
            $notify = new ModerarionApplications(implode(',', $proxy_leads_ids), $this->proxyLeadSettings);
            Mail::queue($notify);
            Mail::assertQueued(ModerarionApplications::class);
        }

        foreach ($recipients->pluck('email')->all() as $email) {
            $notify = new ModerarionApplications(implode(',', $proxy_leads_ids), $this->proxyLeadSettings, $email);
            Mail::queue($notify);
            Mail::assertQueued(ModerarionApplications::class);
        }
    }

    /** @test */
    public function notify_admin_balance_limit_notification()
    {
        $this->company->balance = -850;
        $this->company->lead_cost = 50;
        $this->company->balance_limit = 200;
        $this->company->amount_limit = 200;
        $this->company->prepayment = true;
        $this->company->balance_stop = 0;
        $this->company->save();

        EmailNotificationSetting::factory()->create([
            'company_id' => $this->company->id,
            'notification_type' => EmailNotification::CUSTOMER_BALANCE,
            'status' => EmailNotificationSetting::STATUS_APPROVED,
        ]);

        $this->createReportLead(); //balance -800, notification goes
        $this->createReportLead(); //balance -850, notification don't goes, already sent
        Mail::assertQueued(AdminBalanceLimitCheck::class, 1);

        $this->company->balance = 2500;
        $this->company->lead_cost = 500;
        $this->company->balance_limit = 0;
        $this->company->amount_limit = 200;
        $this->company->prepayment = true;
        $this->company->save();

        $this->createReportLead(); //balance 2000, no notification
        $this->createReportLead(); //balance 1500, no notification
        $this->createReportLead(); //balance 1000, no notification
        $this->createReportLead(); //balance 500, no notification
        $this->createReportLead(); //balance 0, no notification
        $this->createReportLead(); //balance -500, no notification
        Mail::assertQueued(AdminBalanceLimitCheck::class, 1);

        $this->createReportLead(); //balance -1000, notification
        $this->createReportLead(); //balance -1500, notification don't goes, already sent
        Mail::assertQueued(AdminBalanceLimitCheck::class, 2);
    }

    /** @test */
    public function notify_customer_balance_limit_notification()
    {
        $this->company->balance = 250;
        $this->company->lead_cost = 50;
        $this->company->balance_limit = 200;
        $this->company->amount_limit = 200;
        $this->company->prepayment = true;
        $this->company->balance_send_notification = 0;
        $this->company->save();

        EmailNotificationSetting::factory()->create([
            'company_id' => $this->company->id,
            'notification_type' => EmailNotification::CUSTOMER_BALANCE,
            'status' => EmailNotificationSetting::STATUS_APPROVED,
        ]);

        $this->createReportLead(); //balance 200, notification go
        $this->createReportLead(); //balance 150, notification don't goes, already sent
        Mail::assertQueued(CustomerBalanceNotification::class, 1);

        $this->company->balance = 350;
        $this->company->lead_cost = 50;
        $this->company->balance_limit = 200;
        $this->company->amount_limit = 200;
        $this->company->prepayment = true;
        $this->company->save();

        $this->createReportLead(); //balance 300, notification doesn't go
        $this->createReportLead(); //balance 250, notification doesn't go
        Mail::assertQueued(CustomerBalanceNotification::class, 1);

        $this->createReportLead(); //balance 200, notification go
        $this->createReportLead(); //balance 150, notification don't goes, already sent
        Mail::assertQueued(CustomerBalanceNotification::class, 2);
    }

    /**
     * @return \App\Domain\ProxyLead\Models\PlReportLead
     */
    protected function createReportLead()
    {
        $this->truncate(PlReportLead::class, false);

        $params = [
            'api_key' => $this->proxyLeadSettings->public_key,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'title' => 'new_order',
        ];

        $response = $this->get($this->getStoreLink($params));

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => $params['title']]);

        $data = $response->json()['data'];

        $this->assertNotEmpty($data['report_lead']['id']);

        /** @var PlReportLead $reportLead */
        $reportLead = PlReportLead::find($data['report_lead']['id']);
        $this->assertNotEmpty($reportLead);

        return $reportLead;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getStoreLink($params = [])
    {
        return route('api.v1.company.proxy-lead.store').'?'.http_build_query($params);
    }

    /**
     * @return string
     */
    protected function getUpdateEmailableLink()
    {
        return route('account.company.proxy-lead.report.emailable.update', [
            'company' => $this->company,
            'proxyLead' => $this->proxyLead->id,
        ]);
    }
}
