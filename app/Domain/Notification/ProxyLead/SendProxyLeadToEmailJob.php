<?php

namespace App\Domain\Notification\ProxyLead;

use App\Domain\Notification\Mail\ProxyLeadSendFailMail;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\ProxyLeadEmailSender;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendProxyLeadToEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSetting;
    /**
     * @var ProxyLead
     */
    private $proxyLead;

    /**
     * Create a new job instance.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSetting
     * @param ProxyLead $proxyLead
     */
    public function __construct(ProxyLeadSetting $proxyLeadSetting, ProxyLead $proxyLead)
    {
        $this->proxyLeadSetting = $proxyLeadSetting;
        $this->proxyLead = $proxyLead;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sender = new ProxyLeadEmailSender($this->proxyLeadSetting, $this->proxyLead);

        $sender->send();
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed()
    {
        Mail::send(new ProxyLeadSendFailMail($this->proxyLead, ProxyLeadEmailSender::class));
    }
}
