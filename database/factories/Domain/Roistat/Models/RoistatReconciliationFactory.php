<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatReconciliation;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatReconciliationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatReconciliation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     * @throws Exception
     */
    public function definition()
    {
        $types = RoistatReconciliation::getTypes();
        $type = $types[random_int(0, count($types) - 1)];
        $date = Carbon::parse($this->faker->dateTimeBetween('-1 year')->format('Y-m-d'));

        return [
            'roistat_company_config_id' => function () {
                return RoistatCompanyConfig::factory()->create()->id;
            },
            'type' => $type,
            'period' => $date->startOfMonth()->toDateString()
        ];
    }
}
