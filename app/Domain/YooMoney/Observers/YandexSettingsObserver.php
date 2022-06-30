<?php

namespace App\Domain\YooMoney\Observers;

use App\Domain\YooMoney\Models\YandexSetting;
use Illuminate\Support\Facades\Crypt;

class YandexSettingsObserver
{
    public function saving(YandexSetting $settings)
    {
        if (in_array('wallet_number', YandexSetting::SENSITIVE_FIELDS)) {
            $settings->wallet_number = Crypt::encryptString($settings->wallet_number);
        }
        if (in_array('secret_key', YandexSetting::SENSITIVE_FIELDS)) {
            $settings->secret_key = Crypt::encryptString($settings->secret_key);
        }
    }

    public function retrieved(YandexSetting $settings)
    {
        foreach (YandexSetting::SENSITIVE_FIELDS as $field) {
            if ($settings->$field) {
                $settings->$field = Crypt::decryptString($settings->$field);
            }
        }
    }
}
