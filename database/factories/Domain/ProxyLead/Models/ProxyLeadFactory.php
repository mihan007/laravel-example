<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProxyLeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProxyLead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'proxy_lead_setting_id' => ProxyLeadSetting::factory()->create()->id,
            'phone' => '+7' . $this->faker->randomNumber(5) . $this->faker->randomNumber(5),
            'title' => $this->faker->words(3, true),
            'name' => $this->faker->name,
            'comment' => $this->faker->sentence,
            'ym_counter' => $this->faker->randomNumber(4),
            'deleted_at' => null
        ];
    }
}
