<?php

namespace App\Domain\Roistat\Jobs;

use App\Domain\ProxyLead\RoistatDuplicateChecker;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RoistatProxyLeadDuplicateChecker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var \App\Domain\Roistat\Models\RoistatProxyLead
     */
    private $lead;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RoistatProxyLead $lead)
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
        (new RoistatDuplicateChecker($this->lead))->check();
    }
}
