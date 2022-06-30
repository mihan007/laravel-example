<?php

namespace App\Console\Commands;

use App\Domain\ProxyLead\CheckProxyleadPayment;
use Illuminate\Console\Command;

class CheckProxyLeadPaymentForDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxyLead:check {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check proxy-lead payment and status for date';

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
     * @return mixed
     */
    public function handle()
    {
        (new CheckProxyleadPayment($this->argument('date')))
            ->check($this);

        return true;
    }
}
