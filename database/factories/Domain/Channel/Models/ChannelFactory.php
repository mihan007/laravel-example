<?php

namespace Database\Factories\Domain\Channel\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Channel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'slug' => $this->faker->unique()->regexify('[A-Za-z0-9]{20}'),
            'account_id' => Account::factory()->create()->id
        ];
    }
}
