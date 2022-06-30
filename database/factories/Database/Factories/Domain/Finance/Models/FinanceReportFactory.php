<?php

namespace Database\Factories\Domain\Finance\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\FinanceReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinanceReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FinanceReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $leads = $this->faker->numberBetween(200, 1500);
        $leadCost = $this->faker->numberBetween(100, 200);

        return [
            'company_id' => function () {
                return Company::factory()->create()->id;
            },
            'status' => $this->faker->numberBetween(1, 5),
            'lead_count' => $leads,
            'paid' => 0,
            'lead_cost' => $leadCost,
            'to_pay' => $leads * $leadCost,
            'for_date' => now()->startOfMonth()->subMonth()->toDateString()
        ];
    }
}
