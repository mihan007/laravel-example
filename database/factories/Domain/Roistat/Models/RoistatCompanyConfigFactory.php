<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatCompanyConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatCompanyConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => function () {
                return Company::factory()->create()->id;
            },
            'roistat_project_id' => $this->faker->numberBetween(1, 1000),
            'api_key' => $this->faker->md5,
            'timezone' => '+0' . $this->faker->numberBetween($min = 0, $max = 12) . '00',
            'google_limit_amount' => $this->faker->randomFloat(2, 0, 5000),
            'max_lead_price' => $this->faker->randomFloat(2, 0, 5000),
            'max_costs' => $this->faker->randomFloat(2, 0, 5000),
            'avito_visits_limit' => $this->faker->randomNumber(3)
        ];
    }
}
