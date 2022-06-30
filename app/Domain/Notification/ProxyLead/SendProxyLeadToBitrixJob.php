<?php

namespace App\Domain\Notification\ProxyLead;

use App\Domain\Bitrix\Sender;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendProxyLeadToBitrixJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSettings;
    /**
     * @var ProxyLead
     */
    private $proxyLead;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ProxyLeadSetting $proxyLeadSettings, ProxyLead $proxyLead)
    {
        $this->proxyLeadSettings = $proxyLeadSettings;
        $this->proxyLead = $proxyLead;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new Sender($this->proxyLeadSettings, $this->proxyLead))->send();
    }
}
