<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AccountStatement extends Command
{
    protected $signature = 'tinkoff:account-statement';

    protected $description = 'Receiving an account statement';

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
        (new TinkoffApi())->accountStatement();
    }
}
