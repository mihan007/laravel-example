<?php

namespace Database\Seeders;

use App\Domain\Account\Models\AboutCompany;
use App\Domain\Account\Models\AccountSetting;
use Illuminate\Database\Seeder;

class AccountSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('account_settings')->truncate();
        DB::table('about_companies')->truncate();

        $account_settings = AccountSetting::create();
        $about_company = AboutCompany::create();

        $account_settings->is_active = 1;
        $account_settings->bank = 'АО "ТИНЬКОФФ БАНК"';
        $account_settings->bik = '044525974';
        $account_settings->k_account = '30101810145250000974';
        $account_settings->r_account = '40802810600000408982';
        $account_settings->account_id = 3;
        $account_settings->save();

        $about_company->name = 'ИП ЕМЕЛЬЯНОВ ВИКТОР ВЛАДИМИРОВИЧ';
        $about_company->u_name = 'ИП ЕМЕЛЬЯНОВ ВИКТОР ВЛАДИМИРОВИЧ';
        $about_company->inn = '561112781701';
        $about_company->index = '460021';
        $about_company->city = 'РОССИЯ, ОРЕНБУРГСКАЯ ОБЛ, Г ОРЕНБУРГ';
        $about_company->address = 'УЛ ВОСТОЧНАЯ, Д 44';
        $about_company->head = 'ЕМЕЛЬЯНОВ ВИКТОР ВЛАДИМИРОВИЧ';
        $about_company->accountant = 'ЕМЕЛЬЯНОВ ВИКТОР ВЛАДИМИРОВИЧ';

        $d = DIRECTORY_SEPARATOR;
        $about_company->seal_img = $about_company->saveImage('', public_path('images'.$d.'logos'.$d.'stamp.jpg'), 'png');
        $about_company->head_sign = $about_company->saveImage('', public_path('images'.$d.'logos'.$d.'signature.png'), 'png');

        $about_company->account_id = 3;
        $about_company->save();
    }
}
