<?php

namespace Database\Factories\Domain\Finance\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory()->create()->id,
            'amount' => $this->faker->numberBetween(1, 1000),
            'payment_type' => 'inside',
            'status' => 'not_paid'
        ];
    }
}
