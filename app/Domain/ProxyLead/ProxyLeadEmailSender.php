<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 07.06.2018
 * Time: 16:47.
 */

namespace App\Domain\ProxyLead;

use App\Domain\Notification\Mail\NewProxyLeadMail;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Doctrine\Common\Collections\Collection;
use Illuminate\Support\Facades\Mail;

class ProxyLeadEmailSender
{
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSetting;

    /**
     * @var Collection|\Illuminate\Database\Eloquent\Collection
     */
    private $recipients;
    /**
     * @var ProxyLead
     */
    private $proxyLead;

    /**
     * ProxyLeadEmailSender constructor.
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSetting
     */
    public function __construct(ProxyLeadSetting $proxyLeadSetting, ProxyLead $proxyLead)
    {
        $this->proxyLeadSetting = $proxyLeadSetting;
        $this->recipients = $proxyLeadSetting->company->recipientsNotifications()->get();
        $this->proxyLead = $proxyLead;
    }

    /**
     * Send mail about new proxy lead to recipients.
     */
    public function send()
    {
        if ($this->recipients->isEmpty()) {
            Mail::send(new NewProxyLeadMail($this->proxyLead));

            return Mail::failures() ?: true;
        }

        $emails = $this->recipients->pluck('email')->all();

        foreach ($emails as $email) {
            Mail::send(new NewProxyLeadMail($this->proxyLead, $email));
            Mail::failures() ?: true;
        }
    }
}
