<?php

namespace App\Console\Commands;

use App\Domain\Knam\Services\KnamService;
use App\Domain\ProxyLead\Services\LeadService;
use Illuminate\Console\Command;

class ImportKnamLeads extends Command
{
    const KNAM_SERVICE_NAME = 'https://knam.pro';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knam:import 
                            {startDate? : the date since we should grab leads, by default start of current month} 
                            {endDate? : the date until what we should grab leads, by default current time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import knam leads';
    /**
     * @var \App\Domain\Knam\Services\KnamService
     */
    private $knamService;
    /**
     * @var LeadService
     */
    private $leadService;
    /**
     * @var array|string
     */
    private $startDate;
    /**
     * @var array|string
     */
    private $endDate;

    /**
     * Create a new command instance.
     *
     * @param \App\Domain\Knam\Services\KnamService $knamService
     */
    public function __construct(KnamService $knamService, LeadService $leadService)
    {
        $this->knamService = $knamService;
        $this->leadService = $leadService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! config('knam.mvpCompanyId')) {
            throw new \RuntimeException('Please specify KNAM_MVP_COMPANY_ID at .env');
        }
        $this->startDate = $this->argument('startDate') ?: config('knam.startDate');
        $this->endDate = $this->argument('endDate');
        $phones = $this->knamService->getPhonesByPeriod($this->startDate, $this->endDate) ?? [];
        foreach ($phones as $phoneInfo) {
            $this->leadService
                ->initWithCompanyId(config('knam.mvpCompanyId'))
                ->setPhone($phoneInfo['phone'])
                ->setAdvertisingPlatform(self::KNAM_SERVICE_NAME)
                ->setServiceId($phoneInfo['id'])
                ->setTitle("{$phoneInfo['visit_time']} пользователь посетил сайт проекта {$phoneInfo['s_title']}")
                ->setRawContent($phoneInfo)
                ->setLeadPrice(config('knam.leadPrice'))
                ->createOrSkip();
        }
    }
}
