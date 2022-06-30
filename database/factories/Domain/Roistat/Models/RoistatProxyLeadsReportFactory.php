<?php

namespace Database\Factories\Domain\Roistat\Models;

use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\Models\RoistatProxyLead;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoistatProxyLeadsReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoistatProxyLeadsReport::class;

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
            'roistat_proxy_lead_id' => function () {
                return RoistatProxyLead::factory()->create()->id;
            },
            'title' => $this->faker->words(3, true),
            'text' => $this->faker->paragraph,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'roistat' => $this->faker->randomNumber(3),
            'order_id' => $this->faker->randomNumber(3),
            'for_date' => now()->toDateString()
        ];
    }
}
