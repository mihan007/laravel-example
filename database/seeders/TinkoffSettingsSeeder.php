<?php

namespace Database\Seeders;

use App\Domain\Tinkoff\Models\TinkoffSetting;
use Illuminate\Database\Seeder;

class TinkoffSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tinkoff_settings')->truncate();
        $ts = TinkoffSetting::create();
        $ts->is_active = true;
        $ts->account = '40802810600000408982';
        $ts->inn = '561112781701';
        $ts->token = '/Ll0jLqAOw02';
        $ts->account_id = 3;
        $ts->save();
    }
}
