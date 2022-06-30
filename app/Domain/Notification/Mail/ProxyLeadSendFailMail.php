<?php

namespace App\Domain\Notification\Mail;

use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ProxyLeadSendFailMail extends SubscriptionMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
     */
    public $proxyLead;
    public $source;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ProxyLead $proxyLead, $sourсe)
    {
        $this->proxyLead = $proxyLead;
        $this->proxyLead->load('proxyLeadSetting.company');
        $this->source = $sourсe;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->proxyLead->company->account->warningEmail;

        return $this->to($email)
            ->subject('Прокси лид не был отправлен')
            ->view('emails.proxy-lead.fail');
    }

    public function getNotificationType()
    {
        return true;
    }

    public function getCompanyId()
    {
        return true;
    }
}
