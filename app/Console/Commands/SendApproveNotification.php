<?php

namespace App\Console\Commands;

use App\Domain\Company\ApproveNotificationSender;
use Illuminate\Console\Command;

class SendApproveNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:approve-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send approve notification to companies';

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
        $period = now()->startOfMonth()->subMonth();

        (new ApproveNotificationSender($period))->send();
    }
}
