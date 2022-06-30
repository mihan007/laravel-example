<?php

namespace App\Domain\Tinkoff\Observers;

use App\Domain\Tinkoff\Models\TinkoffSetting;
use Illuminate\Support\Facades\Crypt;

class TinkoffSettingsObserver
{
    public function saving(TinkoffSetting $settings)
    {
        $settings->account = Crypt::encryptString($settings->account);
        $settings->token = Crypt::encryptString($settings->token);
    }

    public function retrieved(TinkoffSetting $settings)
    {
        foreach (TinkoffSetting::SENSITIVE_FIELDS as $field) {
            if ($settings->$field) {
                $settings->$field = Crypt::decryptString($settings->$field);
            }
        }
    }
}
