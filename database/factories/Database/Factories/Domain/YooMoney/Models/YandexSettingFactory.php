<?php

namespace Database\Factories\Domain\YooMoney\Models;

use App\Domain\YooMoney\Models\YandexSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class YandexSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = YandexSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'secret_key' => env('SECRET_KEY_YANDEX_MONEY'),
            'is_active' => 1,
            'wallet_number' => env('WALLET_NUMBER_YANDEX_MONEY'),
        ];
    }
}
