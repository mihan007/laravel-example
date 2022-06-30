<?php

namespace Database\Factories\Domain\YandexDirect\Models;

use App\Domain\Company\Models\Company;
use App\Domain\YandexDirect\Models\YandexDirectBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

class YandexDirectBalanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = YandexDirectBalance::class;

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
            'amount' => $this->faker->randomFloat(2, 0, 1000)
        ];
    }
}
