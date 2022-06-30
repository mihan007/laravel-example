<?php

namespace Database\Factories\Domain\Support\Models;

use App\Support\Models\ScheduleTaskLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleTaskLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ScheduleTaskLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }
}
