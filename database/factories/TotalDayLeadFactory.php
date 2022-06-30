<?php

namespace Database\Factories;

use App\Models\TotalDayLead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TotalDayLeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TotalDayLead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ['amount' => $this->faker->randomNumber(2), 'for_date' => Carbon::now()->format('Y-m-d')];
    }
}
