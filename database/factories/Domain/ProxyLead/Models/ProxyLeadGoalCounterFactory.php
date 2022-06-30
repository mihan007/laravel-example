<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLeadGoalCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProxyLeadGoalCounterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProxyLeadGoalCounter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory()->create()->id,
            'target' => $this->faker->numberBetween(1, 100),
            'not_target' => $this->faker->numberBetween(1, 100),
            'not_confirmed' => $this->faker->numberBetween(1, 100),
            'user_not_confirmed' => $this->faker->numberBetween(1, 100),
            'admin_not_confirmed' => $this->faker->numberBetween(1, 100),
            'for_date' => $this->faker->date(),
            'lead_cost' => $this->faker->numberBetween(1, 100)
        ];
    }
}
