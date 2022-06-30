<?php

namespace Database\Factories\Domain\Finance\Models;

use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'finance_report_id' => function () {
                return FinanceReport::factory()->create()->id;
            },
            'amount' => $this->faker->randomFloat(2, 10000, 1000)
        ];
    }
}
