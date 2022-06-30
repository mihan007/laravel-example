<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HideLeadsForCompaniesWithLowBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:hide-leads-if-low-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hide leads for companies with low balance';

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
        $sql = <<<SQL
update companies
set date_stop_leads = now()
where balance < amount_limit and (date_stop_leads is null);
SQL;
        $count = DB::update($sql);
        if ($count) {
            $this->output->writeln("Leads hidden for $count companies");
        }
        return 0;
    }
}
