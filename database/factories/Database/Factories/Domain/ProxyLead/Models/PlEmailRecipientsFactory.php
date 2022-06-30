<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\PlEmailRecipients;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlEmailRecipientsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlEmailRecipients::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = [PlEmailRecipients::TYPE_ADMIN, PlEmailRecipients::TYPE_RECEIVER];

        return [
            'proxy_lead_setting_id' => function () {
                return ProxyLeadSetting::factory()->create()->id;
            },
            'email' => $this->faker->email,
            'type' => $types[$this->faker->numberBetween(0, 1)]
        ];
    }
}
