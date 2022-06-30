<?php

namespace App\Console\Commands;

use App\Domain\Roistat\CheckDimensionsValues;
use Illuminate\Console\Command;

class CheckRoistatAnalyticsDimensionsValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roistat:analyticsDimensionsValues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for dimensions values';

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
        return (new CheckDimensionsValues())->check();
    }
}
