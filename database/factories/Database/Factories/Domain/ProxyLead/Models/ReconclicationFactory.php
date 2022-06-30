<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\Models\Reconclication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReconclicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reconclication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = Reconclication::getTypes()[array_rand(Reconclication::getTypes())];

        return [
            'proxy_lead_setting_id' => function () {
                return ProxyLeadSetting::factory()->create()->id;
            },
            'type' => $type,
            'period' => now()->subMonth()->startOfMonth()->toDateString()
        ];
    }
}
