<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\PlApprovedReport;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlApprovedReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlApprovedReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'proxy_lead_setting_id' => function () {
                return ProxyLeadSetting::factory()->create()->id;
            },
            'for_date' => now()->startOfMonth()->toDateString()
        ];
    }
}
