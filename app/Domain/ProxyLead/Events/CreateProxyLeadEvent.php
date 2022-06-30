<?php

namespace App\Domain\ProxyLead\Events;

use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateProxyLeadEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    public $proxyLeadSetting;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
     */
    public $proxyLead;

    /**
     * Create a new event instance.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSetting
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     */
    public function __construct(ProxyLeadSetting $proxyLeadSetting, ProxyLead $proxyLead)
    {
        $this->proxyLeadSetting = $proxyLeadSetting;
        $this->proxyLead = $proxyLead;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
