<?php

namespace App\Console\Commands;

use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PageSpeed\Insights\Service;

class SitePagespeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:site-pagespeed {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Getting google pagespeed score for site';

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
        $siteId = $this->argument('site');

        $site = Site::find($siteId);

        if (empty($site)) {
            $this->error('Site not found');

            return true;
        }

        $pageSpeed = new Service();
        $desktopInfo = $pageSpeed->getResults($site->url, 'ru');

        if (empty($desktopInfo)) {
            $this->error('Desktop info is empty');

            return true;
        }

        $mobileInfo = $pageSpeed->getResults($site->url, 'ru', 'mobile');

        if (empty($mobileInfo)) {
            $this->error('Mobile info is empty');

            return true;
        }

        $site->mobile_score = $mobileInfo['ruleGroups']['SPEED']['score'];
        $site->mobile_usability = $mobileInfo['ruleGroups']['USABILITY']['score'];
        $site->desktop_score = $desktopInfo['ruleGroups']['SPEED']['score'];
        $site->last_pagespeed_sync = Carbon::now()->toDateTimeString();
        $site->save();

        $this->info("Site {$site->id} score was successful updated");
    }
}
