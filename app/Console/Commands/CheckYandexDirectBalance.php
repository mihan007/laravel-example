<?php

namespace App\Console\Commands;

use App\Domain\YandexDirect\Api\ApiStrategy;
use App\Domain\YandexDirect\CheckCompaniesBalance;
use Illuminate\Console\Command;

class CheckYandexDirectBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yandex:checkBalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run script that checks current balance of all companies that have yandex direct';

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
        $api = (new ApiStrategy())->get();

        (new CheckCompaniesBalance($api))->check();

        return true;
    }
}
