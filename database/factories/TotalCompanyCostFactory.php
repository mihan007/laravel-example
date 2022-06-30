<?php

namespace Database\Factories;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\TotalCompanyCost;
use Illuminate\Database\Eloquent\Factories\Factory;

class TotalCompanyCostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TotalCompanyCost::class;

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
            'amount' => round($this->faker->randomFloat(4, 0, 2000), 2)
        ];
    }
}
