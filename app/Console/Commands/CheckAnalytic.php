<?php

namespace App\Console\Commands;

use App\Domain\Roistat\RoistatAnalytic\CheckGoogleAnalytic;
use App\Domain\Roistat\RoistatAnalytic\CheckYandexAnalytics;
use Illuminate\Console\Command;

class CheckAnalytic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roistat:sync {startDate} {endDate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync roistat for period';

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
        $this->alert('Sync Google Data');
        (new CheckGoogleAnalytic($this->argument('startDate'), $this->argument('endDate')))
            ->check($this);

        $this->alert('Sync Yandex Data');
        (new CheckYandexAnalytics($this->argument('startDate'), $this->argument('endDate')))
            ->check($this);

        return true;
    }
}
