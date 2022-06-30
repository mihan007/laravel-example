<?php

namespace Database\Factories\Domain\Company\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $channel = Channel::factory()->create();

        return [
            'channel_id' => $channel->id,
            'account_id' => $channel->account_id,
            'public_id' => $this->faker->uuid,
            'name' => $this->faker->name,
            'description' => $this->faker->sentence(),
            'check_for_graph' => 1,
            'deleted_at' => null,
            'lead_cost' => $this->faker->numberBetween(0, 200),
            'manage_subscription_key' => $this->faker->words(3, true),
        ];
    }

    public function account(Account $account)
    {
        return $this->state(function (array $attributes) use ($account) {
            return [
                'account_id' => $account
            ];
        });
    }
}
