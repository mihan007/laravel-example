<?php

namespace Database\Factories\Domain\Channel\Models;

use App\Domain\Channel\Models\Channel;
use App\Domain\Channel\Models\ChannelReasonsOfRejection;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelReasonsOfRejectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChannelReasonsOfRejection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'channel_id' => function () {
                return Channel::factory()->create()->id;
            },
            'reasons_of_rejection_id' => function () {
                return ReasonsOfRejection::factory()->create()->id;
            },
        ];
    }
}
