<?php

namespace App\Console\Commands;

use App\Domain\Company\Report\CompanyReportBuilder;
use Illuminate\Console\Command;

class BuildCompanyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:build {startDate} {endDate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build company report';

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
        $this->alert('Start build data with ['.$this->argument('startDate').' - '.$this->argument('endDate').']');
        (new CompanyReportBuilder($this->argument('startDate'), $this->argument('endDate')))
            ->buildReport();
        $this->alert('Done');

        return true;
    }
}
