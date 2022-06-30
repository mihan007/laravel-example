<?php

namespace Tests\Unit;

use App\Domain\Channel\Models\Channel;
use App\Domain\Channel\Models\ChannelReasonsOfRejection;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    /** @test */
    public function it_has_many_reasons_of_rejections()
    {
        $channel = Channel::factory()->create();

        ChannelReasonsOfRejection::factory()->count(3)->create(['channel_id' => $channel->id]);

        $channel->load('channelReasonsOfRejection.reasonOrRejection');

        $this->assertEquals(3, $channel->getReasonsOfRejection()->count());
    }
}
