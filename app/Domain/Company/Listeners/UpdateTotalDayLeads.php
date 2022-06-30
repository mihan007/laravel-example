<?php

namespace App\Domain\Company\Listeners;

use App\Domain\Company\Events\CompaniesAnalyticsReceived;
use Artisan;

class UpdateTotalDayLeads
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CompaniesAnalyticsReceived  $event
     * @return void
     */
    public function handle(CompaniesAnalyticsReceived $event)
    {
        Artisan::call('lead:week');
    }
}
