<?php

namespace App\Support\Jobs;

use App\Domain\Notification\Mail\JobsIsFineNotification;
use App\Domain\Notification\Mail\JobsReachedLimitNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JobsCountChecker
{
    const MAX_JOBS_COUNT = 30;
    const OK_JOBS_COUNT = 10;
    const CACHE_KEY = 'job-counter-checker-arised';

    private $emails;

    public function __construct()
    {
        $this->emails = [
            env('EMAIL_ADMIN', '1@troiza.net'),
        ];
    }

    /**
     * Check analytics information.
     */
    public function check()
    {
        $jobs_count = $this->getJobsCount();
        $weHaveSentNotificationEmail = Cache::has(self::CACHE_KEY);
        echo 'jobs_count'.': '.($jobs_count).PHP_EOL;
        echo 'weHaveSentNotificationEmail'.': '.($weHaveSentNotificationEmail ? 'true' : 'false').PHP_EOL;
        if (! $weHaveSentNotificationEmail && ($jobs_count >= self::MAX_JOBS_COUNT)) {
            foreach ($this->emails as $email) {
                \Mail::send(new JobsReachedLimitNotification($email, $jobs_count));
                Cache::forever(self::CACHE_KEY, time());
            }
            echo "Sent JobsReachedLimitNotification, jobs: {$jobs_count}".PHP_EOL;
        }
        if ($weHaveSentNotificationEmail && ($jobs_count <= self::OK_JOBS_COUNT)) {
            $whenWeReachedMax = Cache::pull(self::CACHE_KEY);
            foreach ($this->emails as $email) {
                \Mail::send(new JobsIsFineNotification($email, $jobs_count, $whenWeReachedMax));
            }
            echo "Sent JobsIsFineNotification, jobs: {$jobs_count}".PHP_EOL;
        }
    }

    private function getJobsCount()
    {
        return DB::table('jobs')->count();
    }
}
