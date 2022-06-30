<?php

namespace App\Console\Commands;

use App\Domain\ProxyLead\Events\StatusProxyLeadEvent;
use Illuminate\Console\Command;

class ProxyLeadsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxyLead:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all status proxy leads';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         // Add all status
        event(new StatusProxyLeadEvent());
    }
}
