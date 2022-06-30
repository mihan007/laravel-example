<?php

namespace Database\Factories\Domain\ProxyLead\Models;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProxyLeadSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProxyLeadSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory()->create()->id,
            'public_key' => Str::random(20)
        ];
    }
}
