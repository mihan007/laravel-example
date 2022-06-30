<?php

namespace Tests\Unit;

use App\Domain\Channel\Models\Channel;
use App\Domain\User\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * Class UserTest.
 */
class UserTest extends TestCase
{
    /** @test */
    public function it_can_get_companies_channel_cache_name()
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = User::factory()->create();

        $this->assertSame("user.{$user->id}.companies_channel", $user->companiesChannel());
    }

    /** @test */
    public function it_can_get_companies_channel()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $channel = Channel::factory()->create();
        $user->setCompaniesChannel($channel);

        $this->assertSame($channel->slug, $user->getCompaniesChannel());
    }

    /** @test */
    public function it_can_refresh_companies_channel()
    {
        $channels = Channel::factory()->count(2)->create();

        /** @var Request|Mockery\Mock $request */
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('has')
            ->times(2)
            ->with('channel')
            ->andReturn(true);

        $request->shouldReceive('get')
            ->with('channel')
            ->times(2)
            ->andReturn($channels->first()->slug, $channels->get(1)->slug);

        /** @var User $user */
        $user = User::factory()->create();

        $this->assertEquals($channels->first()->toArray(), $user->refreshCompaniesChannel($request)->toArray());
        $this->assertSame($channels->first()->slug, $user->getCompaniesChannel());

        $this->assertEquals($channels->get(1)->toArray(), $user->refreshCompaniesChannel($request)->toArray());
        $this->assertSame($channels->get(1)->slug, $user->getCompaniesChannel());
    }

    /** @test */
    public function refresh_companies_channel_function_with_channel_clear_will_clear_stored_channel()
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = User::factory()->create();
        $channel = Channel::factory()->create();
        $user->setCompaniesChannel($channel);

        $this->assertSame($channel->slug, $user->getCompaniesChannel());

        /** @var Request|Mockery\Mock $request */
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('has')
            ->times(1)
            ->with('channel')
            ->andReturn(true);

        $request->shouldReceive('get')
            ->with('channel')
            ->times(1)
            ->andReturn('_clear');

        $user->refreshCompaniesChannel($request);

        $this->assertNull($user->getCompaniesChannel());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
