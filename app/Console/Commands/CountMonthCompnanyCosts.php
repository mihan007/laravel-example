<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CountMonthCompnanyCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:countCosts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count costs in current month for each companies';

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
        return (new CompanyCostsCounter())->make();
    }
}
