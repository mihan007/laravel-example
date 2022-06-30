<?php

namespace App\Domain\Notification\Observers;

use App\Domain\Notification\Models\EmailNotificationSetting;
use Illuminate\Support\Str;

class EmailNotificationSettingObserver
{
    public function creating(EmailNotificationSetting $setting)
    {
        $setting->disable_link_key = Str::random(64);
    }
}
