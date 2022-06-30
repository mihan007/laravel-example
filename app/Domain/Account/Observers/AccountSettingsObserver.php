<?php

namespace App\Domain\Account\Observers;

use App\Domain\Account\Models\AccountSetting;
use Illuminate\Support\Facades\Crypt;

class AccountSettingsObserver
{
    public function saving(AccountSetting $settings)
    {
        if (in_array('bik', AccountSetting::SENSITIVE_FIELDS)) {
            $settings->bik = Crypt::encryptString($settings->bik);
        }
        if (in_array('k_account', AccountSetting::SENSITIVE_FIELDS)) {
            $settings->k_account = Crypt::encryptString($settings->k_account);
        }
        if (in_array('r_account', AccountSetting::SENSITIVE_FIELDS)) {
            $settings->r_account = Crypt::encryptString($settings->r_account);
        }
    }

    public function retrieved(AccountSetting $settings)
    {
        foreach (AccountSetting::SENSITIVE_FIELDS as $field) {
            if ($settings->$field) {
                $settings->$field = Crypt::decryptString($settings->$field);
            }
        }
    }
}
