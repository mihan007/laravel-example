<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatProxyLeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatProxyLead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween(
            Carbon::now()->startOfMonth()->subMonth()->toDateString(),
            $endDate = 'now'
        );

        return [
            'company_id' => function () {
                return Company::factory()->create()->id;
            },
            'roistat_id' => $this->faker->randomNumber(5),
            'title' => 'Заявка с сайта',
            'text' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'name' => $this->faker->name,
            'phone' => $this->faker->randomNumber(6) . $this->faker->randomNumber(5),
            'email' => $this->faker->email,
            'roistat' => $this->faker->randomNumber(7),
            'creation_date' => $date,
            'order_id' => $this->faker->randomNumber(5),
            'for_date' => date('Y-m-d', $date->getTimestamp())
        ];
    }
}
