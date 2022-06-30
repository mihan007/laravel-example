<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlReportLeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlReportLead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'proxy_lead_id' => function () {
                return ProxyLead::factory()->create()->id;
            },
            'reasons_of_rejection_id' => function () {
                return ReasonsOfRejection::factory()->create()->id;
            }
        ];
    }
}
