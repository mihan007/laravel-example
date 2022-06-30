<?php

namespace Database\Factories\Domain\Zadarma\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Zadarma\Models\ZadarmaCompanyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZadarmaCompanyConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ZadarmaCompanyConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory()->create()->id,
            'key' => $this->faker->uuid,
            'secret' => $this->faker->password
        ];
    }
}
