<?php

namespace App\Console\Commands;

use App\Domain\Company\CompaniesHealthyAnalyzer;
use Illuminate\Console\Command;

class MailNotifyMain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:notify-main';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify companies with main information';

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
        return (new CompaniesHealthyAnalyzer())->analyze();
    }
}
