<?php

namespace App\Domain\Bitrix;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use DateTime;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait BitrixLogTrait
{
    private function log($payloadData, $state, ProxyLeadSetting $proxyLeadSettings, ProxyLead $proxyLead, $level = Logger::INFO)
    {
        $data = [
            'date' => (new DateTime())->format(DateTime::W3C),
            'state' => $state,
            'url' => $proxyLeadSettings->bitrix_webhook,
            'proxyLeadSettingsId' => $proxyLeadSettings->id,
            'company' => $proxyLeadSettings->company->name.("#{$proxyLeadSettings->company->id}"),
            'proxyLeadInfo' => $proxyLead->attributesToArray(),
            'payload' => $payloadData,
        ];
        $emailLog = new Logger('bitrix');
        $emailLog->pushHandler(new StreamHandler(storage_path('logs/bitrix.log')), $level);
        $emailLog->info($state, $data);
    }
}
