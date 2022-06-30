<?php

namespace App\Domain\Notification\Mail;

use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class WrongProxyLeadMail extends SubscriptionMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var ProxyLead
     */
    public $proxyLead;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ProxyLead $proxyLead)
    {
        $this->proxyLead = $proxyLead;
        $this->proxyLead->load('proxyLeadSetting.company');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
    return $this->subject('Некорректная заявка с сайта')
            ->markdown('emails.proxy-lead.wrong');
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        // TODO: Implement getCompanyId() method.
    }
}
