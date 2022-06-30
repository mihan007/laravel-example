<?php

namespace App\Domain\ProxyLead\Listeners;

use App\Domain\ProxyLead\Events\WrongProxyLeadPayloadEvent;
use App\Domain\ProxyLead\WrongProxyLeadSender;

class SendWrongProxyLeadListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WrongProxyLeadPayloadEvent $event
     * @return void
     * @throws \Yclients\YclientsException
     */
    public function handle(WrongProxyLeadPayloadEvent $event)
    {
        $sender = new WrongProxyLeadSender($event->proxyLeadSetting, $event->proxyLead);
        $sender->send();
    }
}
