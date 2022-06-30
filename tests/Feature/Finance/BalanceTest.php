<?php

namespace Tests\Feature\Company\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\Payment;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\YooMoney\Models\YandexSetting;
use Faker;
use Tests\TestCase;

/**
 * Class BalanceTest.
 */
class BalanceTest extends TestCase
{
    /** @var Faker\Generator */
    protected $company;

    /** @var \App\Domain\Company\Models\Company */
    protected $faker;

    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSettings;

    /** @var \App\Domain\ProxyLead\Models\ProxyLead */
    protected $proxyLead;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $account;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $yandexSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Payment::class);
        $this->truncate(ProxyLead::class, false);
        $this->truncate(ProxyLeadSetting::class, false);
        $this->truncate(Company::class, false);
        $this->truncate(PaymentTransaction::class, false);
        $this->truncate(Account::class, false);
        $this->truncate(YandexSetting::class, false);

        $this->faker = Faker\Factory::create();

        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);
        $this->yandexSettings = YandexSetting::factory()->create(['account_id' => $this->account->id]);
        $this->proxyLeadSettings = ProxyLeadSetting::factory()->create(['company_id' => $this->company->id]);
        $this->proxyLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSettings->id]);
    }

    /** @test */
    public function has_write_balance()
    {
        $this->company->balance = 100;
        $this->company->lead_cost = 50;
        $this->company->balance_limit = 200;
        $this->company->amount_limit = 200;
        $this->company->prepayment = true;
        $this->company->save();

        $this->createReportLead();
        $this->company->refresh();
        /** @var \App\Domain\Finance\Models\PaymentTransaction $transaction */
        $transaction = PaymentTransaction::where('company_id', $this->company->id)
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotEmpty($transaction);
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals('inside', $transaction->payment_type);
        $this->assertEquals('write-off', $transaction->status);
        $this->assertEquals(50, $this->company->balance);
    }

    /**
     * @test
     */
    public function has_pay_yandex_new_url()
    {
        $this->company->balance = 100;
        $this->company->prepayment = true;
        $this->company->save();

        $transaction = $this->createTransaction(
            [
                'payment_type' => 'yandex_money_pc',
                'amount' => 10,
            ]
        );

        $post = [
            'notification_type' => 'p2p-incoming',
            'bill_id' => '',
            'amount' => 98.95,
            'withdraw_amount' => 99,
            'datetime' => (new \DateTime())->format(DATE_W3C),
            'codepro' => false,
            'sender' => 41001000040,
            'sha1_hash' => '',
            'test_notification' => true,
            'operation_label' => '2521301b-0011-5000-9000-143d82850250',
            'operation_id' => 'test-notification',
            'currency' => 643,
            'label' => $transaction->id,
        ];
        $post['sha1_hash'] = sha1(
            implode(
                '&',
                [
                    $post['notification_type'],
                    $post['operation_id'],
                    $post['amount'],
                    $post['currency'],
                    $post['datetime'],
                    $post['sender'],
                    $post['codepro'],
                    env('SECRET_KEY_YANDEX_MONEY'),
                    $post['label'],
                ]
            )
        );
        $this->post(route('yandex.webhook', ['id' => $this->account->id]), $post);

        $this->company->refresh();
        $transaction->refresh();

        $this->assertEquals(199, $this->company->balance);
        $this->assertEquals(99, $transaction->amount);
        $this->assertEquals('paid', $transaction->status);
        $this->assertEquals('replenishment', $transaction->operation);
    }

    /**
     * @param array $params
     * @return \App\Domain\Finance\Models\PaymentTransaction
     */
    protected function createTransaction(array $params)
    {
        return create(
            PaymentTransaction::class,
            array_merge(
                $params,
                [
                    'company_id' => $this->company->id,
                    'company_name' => $this->faker->company,
                    'company_inn' => $this->faker->randomDigit,
                    'status' => 'not_paid',
                    'operation' => 'write-off',
                ]
            )
        );
    }

    /**
     * @return PlReportLead
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
        return route(
            'account.company.proxy-lead.report.emailable.update',
            [
                'company' => $this->company,
                'proxyLead' => $this->proxyLead->id,
            ]
        );
    }
}
