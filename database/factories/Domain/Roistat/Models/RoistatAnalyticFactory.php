<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Roistat\Models\RoistatAnalytic;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatAnalyticFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatAnalytic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'visitCount' => $this->faker->randomDigit,
            'visits2leads' => $this->faker->randomFloat(2, 0, 500),
            'leadCount' => $this->faker->randomDigit,
            'visitsCost' => $this->faker->randomFloat(2, 0, 500),
            'costPerClick' => $this->faker->randomFloat(2, 0, 500),
            'costPerLead' => $this->faker->randomFloat(2, 0, 500)
        ];
    }
}
