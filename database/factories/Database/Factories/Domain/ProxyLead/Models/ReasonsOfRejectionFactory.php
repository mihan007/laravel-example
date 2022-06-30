<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReasonsOfRejectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReasonsOfRejection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
        ];
    }
}
