<?php

namespace App\Domain\Notification\Observers;

use App\Domain\Notification\Models\EmailManageLink;
use Illuminate\Support\Str;

class EmailManageLinkObserver
{
    public function creating(EmailManageLink $link)
    {
        $link->approve_all_pending_key = Str::random(64);
        $link->notification_settings_key = Str::random(64);
        $link->disable_all_key = Str::random(64);
    }
}
