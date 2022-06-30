<?php

namespace App\Console\Commands;

use App\Domain\Roistat\GoogleAmountEmailNotification;
use App\Domain\Roistat\RoistatBalanceEmailNotification;
use App\Domain\YandexDirect\MailNotifications;
use Illuminate\Console\Command;

class NotifyCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailNotifications:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications about problems to adjusted mails';

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
        $successfulFinished = true;

        if (! (new MailNotifications())->check()) {
            $successfulFinished = false;
        }

        if (! (new GoogleAmountEmailNotification())->check()) {
            $successfulFinished = false;
        }

        if (! (new RoistatBalanceEmailNotification())->check()) {
            $successfulFinished = false;
        }

        return $successfulFinished;
    }
}
