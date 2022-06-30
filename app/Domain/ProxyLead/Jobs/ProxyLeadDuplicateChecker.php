<?php

namespace App\Domain\ProxyLead\Jobs;

use App\Domain\ProxyLead\DuplicateChecker;
use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProxyLeadDuplicateChecker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLead
     */
    private $lead;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ProxyLead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new DuplicateChecker($this->lead))->check();
    }
}
