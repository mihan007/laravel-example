<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SitesPagespeedSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:sites-pagespeed-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get pagespeed information for all sites';

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
        $sites = Site::all();

        if (empty($sites)) {
            return true;
        }

        foreach ($sites as $site) {
            Artisan::call('google:site-pagespeed', ['site' => $site->id]);
        }
    }
}
