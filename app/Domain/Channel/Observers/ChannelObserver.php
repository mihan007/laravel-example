<?php

namespace App\Domain\Channel\Observers;

use App\Domain\Channel\Models\Channel;
use Illuminate\Support\Str;

class ChannelObserver
{
    /**
     * @param \App\Domain\Channel\Models\Channel $channel
     */
    public function creating(Channel $channel)
    {
        do {
            $possibleSlug = Str::random();
        } while (Channel::whereSlug($possibleSlug)->count() > 0);

        $channel->slug = $possibleSlug;
    }
}
