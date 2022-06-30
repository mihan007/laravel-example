<?php

namespace App\Domain\Notification\Mail;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class MailCheckPayment extends SubscriptionMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var ProxyLead
     */
    public $proxyLead;
    public $proxyLeadSetting;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ProxyLead $proxyLead, ProxyLeadSetting $proxyLeadSetting)
    {
        $this->proxyLead = $proxyLead;
        $this->proxyLeadSetting = $proxyLeadSetting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->proxyLead->company->account->warningEmail;

        return $this->subject('Внимание! Пришла заявка, но списание не произошло')
            ->markdown('emails.notification-check-payment')->to($email);
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
