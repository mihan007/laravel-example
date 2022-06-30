<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RcBalanceTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class RcBalanceTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RcBalanceTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ['rc_balance_config_id' => function () {
            return RcBalanceConfig::factory()->create()->id;
        }, 'date' => now(), 'type' => 'type', 'system_name' => 'system_name', 'display_name' => 'display_name', 'project_id' => $this->faker->numberBetween(1, 1000), 'sum' => $this->faker->numberBetween(1, 10000), 'balance' => $this->faker->numberBetween(1, 10000), 'virtual_balance' => $this->faker->numberBetween(1, 10000)];
    }
}
