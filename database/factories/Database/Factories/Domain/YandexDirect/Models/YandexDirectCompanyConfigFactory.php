<?php

namespace Database\Factories\Domain\YandexDirect\Models;

use App\Domain\Company\Models\Company;
use App\Domain\YandexDirect\Models\YandexDirectCompanyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class YandexDirectCompanyConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = YandexDirectCompanyConfig::class;

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
            'yandex_auth_key' => $this->faker->uuid,
            'yandex_login' => $this->faker->name,
            'amount' => $this->faker->numberBetween(0, 1000),
            'token_life_time' => $this->faker->numberBetween(0, 1000),
            'token_added_on' => now(),
            'limit_amount' => 0
        ];
    }
}
