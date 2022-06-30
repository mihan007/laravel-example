<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 06.06.2018
 * Time: 15:37.
 */

namespace App\Domain\ProxyLead;

use App\Domain\Notification\ProxyLead\SendProxyLeadToEmailJob;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;

class ProxyLeadSender
{
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSetting;
    /**
     * @var ProxyLead
     */
    private $proxyLead;

    /**
     * ProxyLeadSender constructor.
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSetting
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     */
    public function __construct(ProxyLeadSetting $proxyLeadSetting, ProxyLead $proxyLead)
    {
        $this->proxyLeadSetting = $proxyLeadSetting;
        $this->proxyLead = $proxyLead;
    }

    /**
     * @return bool
     * @throws \Yclients\YclientsException
     */
    public function send()
    {
        return $this->sendToEmailRecipients();
    }

    public function sendToEmailRecipients()
    {
        SendProxyLeadToEmailJob::dispatch($this->proxyLeadSetting, $this->proxyLead);

        return true;
    }
}
