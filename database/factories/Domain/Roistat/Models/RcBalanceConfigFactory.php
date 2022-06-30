<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RcBalanceConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class RcBalanceConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RcBalanceConfig::class;

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
            'project_id' => $this->faker->numberBetween(1, 1000),
            'api_key' => $this->faker->uuid,
            'limit_amount' => $this->faker->numberBetween(100, 1000)
        ];
    }
}
