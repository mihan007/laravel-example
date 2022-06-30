<?php

namespace App\Domain\Bitrix;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Exception;
use Monolog\Logger;

class Sender
{
    use BitrixLogTrait;

    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $settings;
    /**
     * @var ProxyLead
     */
    private $proxyLead;

    /**
     * Sender constructor.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $settings
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     */
    public function __construct(ProxyLeadSetting $settings, ProxyLead $proxyLead)
    {
        $this->settings = $settings;
        $this->proxyLead = $proxyLead;
    }

    public function send()
    {
        return $this->sendToServer($this->prepareFields());
    }

    private function prepareFields()
    {
        $data = [
            'name' => $this->proxyLead->name,
            'title' => $this->proxyLead->title,
            'phone' => $this->proxyLead->phone,
            'comment' => nl2br($this->proxyLead->comment, false),
        ];

        return $data;
    }

    private function sendToServer(array $data)
    {
        try {
            $payoladData = $this->preparePayload($data);
            $query = http_build_query($payoladData);
            $this->log($payoladData, 'Lead sending prepared', $this->settings, $this->proxyLead);
            $status = file_get_contents($this->settings->bitrix_webhook.'crm.lead.add.json?'.$query);
            $statusReadable = $this->prepareReadableStatus($status);
            $this->log($payoladData, 'Lead sending finished. Result is '.$statusReadable, $this->settings, $this->proxyLead);

            return true;
        } catch (Exception $e) {
            $this->log([], 'Lead sending finished. Result is fail, details: '.$e->getMessage(), $this->settings, $this->proxyLead, Logger::ERROR);

            return false;
        }
    }

    /**
     * @param array $data
     * @return array[]
     */
    private function preparePayload(array $data): array
    {
        return [
            'fields' => [
                'TITLE' => $data['title'],
                'NAME' => $data['name'],
                'STATUS_ID' => 'NEW',
                'OPENED' => 'Y',
                'COMMENTS' => $data['comment'],
                'SOURCE_ID' => 'WEB',
                'SOURCE_DESCRIPTION' => 'Лидогенератор',
                'UTM_SOURCE' => 'leadogenerator',
                'PHONE' => [['VALUE' => $data['phone'], 'VALUE_TYPE' => 'WORK']],
            ],
        ];
    }

    private function prepareReadableStatus(string $status)
    {
        if ($status === false) {
            return 'fail';
        }

        return $status;
    }
}
