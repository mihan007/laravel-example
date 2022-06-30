<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 06.06.2018
 * Time: 15:37.
 */

namespace App\Domain\ProxyLead;

use App\Domain\Notification\Mail\WrongProxyLeadMail;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Mail;

class WrongProxyLeadSender
{
    /**
     * @var ProxyLeadSetting
     */
    private $proxyLeadSetting;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
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
     */
    public function send()
    {
        $extra = $this->proxyLead->extra;
        if (empty($extra)) {
            //no need to send with empty request
            return true;
        }
        $managers = $this->proxyLead->company->getManagers();
        $emails = [];
        foreach ($managers as $manager) {
            $managerUser = User::find($manager);
            if (optional($managerUser)->email) {
                $emails[] = optional($managerUser)->email;
            }
        }
        if (count($emails) === 0) {
            $emails = $this->proxyLead->company->account->warningEmail;
        }
        Mail::to($emails)->send(new WrongProxyLeadMail($this->proxyLead));

        return true;
    }
}
