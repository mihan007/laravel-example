<?php

namespace App\Console\Commands;

use App\Domain\Company\CompaniesTotalLeadsCounter;
use Illuminate\Console\Command;

class GetRecentLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:resent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count recent leads';

    /**
     * Create a new command instance.
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
        return (new CompaniesTotalLeadsCounter())->count();
    }
}
