<?php

namespace App\Console\Commands;

use App\Domain\Roistat\CheckProxyLeads;
use Illuminate\Console\Command;

class CheckRoistatProxyLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roistat:proxyLeads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all proxy lead for yesterday';

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
        return (new CheckProxyLeads())->check();
    }
}
