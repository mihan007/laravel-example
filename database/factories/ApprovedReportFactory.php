<?php

namespace Database\Factories;

use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Models\ApprovedReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovedReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApprovedReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'roistat_company_config_id' => function () {
                return RoistatCompanyConfig::factory()->create()->id;
            },
            'for_date' => now()->subMonth()->startOfMonth()->toDateString()
        ];
    }
}
