<?php

namespace App\Console\Commands;

use App\Domain\Notification\Mail\ModerarionApplicationsAdmin;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;

class ModerationApplicationsAdmin
{
    public function sendModerations()
    {
        $proxy_leads_settings = ProxyLeadSetting::all();

        foreach ($proxy_leads_settings as $proxy_leads_setting) {
            $send_moderation = PlReportLead::leftJoin('proxy_leads', 'pl_report_leads.proxy_lead_id', '=',
                'proxy_leads.id')
                ->select('proxy_leads.id')
                ->where('proxy_leads.proxy_lead_setting_id', $proxy_leads_setting->id)
                ->where('pl_report_leads.admin_confirmed', PlReportLead::STATUS_NOT_CONFIRMED)
                ->get();

            $proxy_leads_ids = [];

            if ($send_moderation->isEmpty()) {
                continue;
            }

            foreach ($send_moderation as $item) {
                $proxy_leads_ids[] = $item->id;
            }

            if (! $proxy_leads_setting->company) {
                continue;
            }

            $plEmails = $proxy_leads_setting->company->mainNotifications()->get();

            if ($plEmails->isEmpty()) {
                \Mail::send(new ModerarionApplicationsAdmin(implode(',', $proxy_leads_ids), $proxy_leads_setting));
            }

            foreach ($plEmails->pluck('email') as $email) {
                \Mail::send(new ModerarionApplicationsAdmin(implode(',', $proxy_leads_ids), $proxy_leads_setting, $email));
            }
        }
    }
}
