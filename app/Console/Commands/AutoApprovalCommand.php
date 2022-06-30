<?php

namespace App\Console\Commands;

use App\Domain\Company\AutoApproval;
use Illuminate\Console\Command;

class AutoApprovalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:auto-approve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect companies without leads and auto approve them';

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
        (new AutoApproval(now()->startOfMonth()->subMonth()))->check();
    }
}
