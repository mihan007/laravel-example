<?php

namespace Tests\Unit;

use App\Domain\YandexDirect\Api\ApiStrategy;
use App\Domain\YandexDirect\Api\PublicApi;
use App\Domain\YandexDirect\Api\TestApi;
use Tests\TestCase;

class ApiStrategyTest extends TestCase
{
    /** @var ApiStrategy */
    protected $strategy;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->strategy = new ApiStrategy();
    }

    /** @test */
    public function it_should_return_public_api_if_there_is_no_flag() :void
    {
        $this->assertInstanceOf(PublicApi::class, $this->strategy->get());
    }

    public function it_should_return_test_api_if_flag_is_true()
    {
        $this->assertInstanceOf(TestApi::class, $this->strategy->get(true));
    }
}
