<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 24.08.2018
 * Time: 10:04.
 */

namespace App\Domain\Finance\Repositories;

use App\Domain\Channel\Models\Channel;
use Illuminate\Database\Eloquent\Builder;

class UserFinanceListRepository extends FinanceListRepository
{
    /**
     * Filter result by company channel.
     *
     * @param Builder $builder
     */
    protected function filterByChannel(Builder $builder): void
    {
        $channel = $this->getChannel();

        if (empty($channel)) {
            return;
        }

        $channelObject = Channel::where('slug', $channel)->firstOrFail();

        $builder->whereHas('company', function (Builder $query) use ($channelObject) {
            $query->where('channel_id', $channelObject->id);
        });
    }
}
