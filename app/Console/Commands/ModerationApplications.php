<?php

namespace App\Console\Commands;

use App\Domain\Notification\Mail\ModerarionApplications;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Carbon\Carbon;

class ModerationApplications
{
    public function __construct()
    {
    }

    public function sendModerations()
    {
        $proxy_leads_settings = ProxyLeadSetting::all();

        foreach ($proxy_leads_settings as $proxy_leads_setting) {
            $check_send_moderation = PlReportLead::leftJoin('proxy_leads', 'pl_report_leads.proxy_lead_id', '=',
                'proxy_leads.id')
                ->select(['pl_report_leads.updated_at', 'pl_report_leads.is_send'])
                ->where('proxy_leads.proxy_lead_setting_id', $proxy_leads_setting->id)
                ->where('pl_report_leads.moderation_status', 1)
                ->where('pl_report_leads.is_send', false)
                ->orderBy('pl_report_leads.updated_at', 'DESC')
                ->first();

            if (! $check_send_moderation) {
                continue;
            }

            if ($check_send_moderation->updated_at->lt(Carbon::now()->subMinutes(20))) {
                $send_moderation = PlReportLead::leftJoin('proxy_leads', 'pl_report_leads.proxy_lead_id', '=',
                    'proxy_leads.id')
                    ->select('proxy_leads.id')
                    ->where('proxy_leads.proxy_lead_setting_id', $proxy_leads_setting->id)
                    ->where('pl_report_leads.moderation_status', 1)
                    ->where('pl_report_leads.is_send', false)
                    ->orderBy('pl_report_leads.updated_at', 'DESC')
                    ->get();

                $proxy_leads_ids = [];
                foreach ($send_moderation as $item) {
                    $proxy_leads_ids[] = $item->id;
                }

                $recipients = $proxy_leads_setting->company->recipientsNotifications()->get();

                if ($recipients->isEmpty()) {
                    \Mail::send(new ModerarionApplications(implode(',', $proxy_leads_ids), $proxy_leads_setting));
                    PlReportLead::whereIn('proxy_lead_id', $proxy_leads_ids)->update([
                        'moderation_status' => 0,
                        'is_send' => true,
                    ]);

                    return true;
                }

                foreach ($recipients->pluck('email')->all() as $email) {
                    \Mail::send(new ModerarionApplications(implode(',', $proxy_leads_ids), $proxy_leads_setting,
                        $email));
                }

                PlReportLead::whereIn('proxy_lead_id', $proxy_leads_ids)->update([
                    'moderation_status' => 0,
                    'is_send' => true,
                ]);
            }
        }
    }
}
