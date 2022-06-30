<?php

namespace Database\Seeders;

use App\Domain\YooMoney\Models\YandexSetting;
use Illuminate\Database\Seeder;

class YandexSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $yandex_setting = YandexSetting::create();
        $yandex_setting->wallet_number = '4100';
        $yandex_setting->secret_key = 'siSgxst';
        $yandex_setting->is_active = 1;
        $yandex_setting->webhook_address = '';
        $yandex_setting->is_yandex_wallet = 1;
        $yandex_setting->is_bank_card = 1;
        $yandex_setting->account_id = 3;
        $yandex_setting->save();
    }
}
