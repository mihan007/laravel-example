<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatGoogleAnalytic;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatGoogleAnalyticFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatGoogleAnalytic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'roistat_company_config_id' => function () {
                return RoistatCompanyConfig::factory()->create()->id;
            },
            'visitCount' => $this->faker->randomNumber(3),
            'visits2leads' => $this->faker->randomFloat(2, 0, 5000),
            'leadCount' => $this->faker->randomNumber(3),
            'visitsCost' => $this->faker->randomFloat(2, 0, 5000),
            'costPerClick' => $this->faker->randomFloat(2, 0, 5000),
            'costPerLead' => $this->faker->randomFloat(2, 0, 5000)
        ];
    }
}
