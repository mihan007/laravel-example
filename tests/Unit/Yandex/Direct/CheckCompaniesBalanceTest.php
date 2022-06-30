<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\YandexDirect\Api\TestApi;
use App\Domain\YandexDirect\CheckCompaniesBalance;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use App\Domain\YandexDirect\Models\YandexDirectCompanyConfig;
use Mockery;
use Tests\TestCase;

class CheckCompaniesBalanceTest extends TestCase
{
    /** @test */
    public function it_should_add_company_yandex_balance(): void
    {
        $this->truncate(Company::class);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(YandexDirectBalance::class, false);

        $company = Company::factory()->create();
        $yandexDirectConfig = YandexDirectCompanyConfig::factory()->create(['company_id' => $company->id]);

        $mockedApi = Mockery::mock(TestApi::class);
        $mockedApi->shouldReceive('makeAccountManagementRequest')
            ->once()
            ->andReturn($this->getYandexApiResponse(200));

        $this->assertSame(0, YandexDirectBalance::count());

        $balanceChecker = new CheckCompaniesBalance($mockedApi);
        $balanceChecker->check();

        $this->assertSame(1, YandexDirectBalance::count());
    }

    /** @test */
    public function it_should_not_add_yandex_balance_if_yandex_is_not_configured(): void
    {
        $this->truncate(Company::class);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(YandexDirectBalance::class, false);

        $company = Company::factory()->create();
        $yandexDirectConfig = YandexDirectCompanyConfig::factory()->create(['company_id' => $company->id, 'yandex_auth_key' => '']);

        $mockedApi = Mockery::mock(TestApi::class);
        $mockedApi->shouldReceive('makeAccountManagementRequest')
            ->never();

        $this->assertSame(0, YandexDirectBalance::count());

        $balanceChecker = new CheckCompaniesBalance($mockedApi);
        $balanceChecker->check();

        $this->assertSame(0, YandexDirectBalance::count());
    }

    /** @test */
    public function it_should_not_add_yandex_balance_if_yandex_config_is_not_set(): void
    {
        $this->truncate(Company::class);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(YandexDirectBalance::class, false);

        $company = Company::factory()->create();

        $mockedApi = Mockery::mock(TestApi::class);
        $mockedApi->shouldReceive('makeAccountManagementRequest')
            ->never();

        $this->assertSame(0, YandexDirectBalance::count());

        $balanceChecker = new CheckCompaniesBalance($mockedApi);
        $balanceChecker->check();

        $this->assertSame(0, YandexDirectBalance::count());
    }

    /** @test */
    public function it_should_works_for_few_companies(): void
    {
        $this->truncate(Company::class);
        $this->truncate(YandexDirectCompanyConfig::class, false);
        $this->truncate(YandexDirectBalance::class, false);

        Company::factory()->count(2)->create()->each(function (Company $company) {
            YandexDirectCompanyConfig::factory()->create(['company_id' => $company->id]);
        });

        $mockedApi = Mockery::mock(TestApi::class);
        $mockedApi->shouldReceive('makeAccountManagementRequest')
            ->twice()
            ->andReturn($this->getYandexApiResponse(200));

        $this->assertSame(0, YandexDirectBalance::count());

        $balanceChecker = new CheckCompaniesBalance($mockedApi);
        $balanceChecker->check();

        $this->assertSame(2, YandexDirectBalance::count());
    }

    private function getYandexApiResponse($amount)
    {
        return [
            'data' => [
                'Accounts' => [
                    [
                        'Amount' => $amount,
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
