<?php

namespace App\Console;

use App\Console\Commands\Notifier;
use App\Console\Commands\ScheduleList;
use App\Console\Commands\SitePagespeedCommand;
use App\Console\Commands\SitesPagespeedSyncCommand;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CheckYandexDirectBalance::class,
        Commands\CheckJobsCount::class,
        Commands\CheckRoistatStatistics::class,
        Commands\CheckRoistatProxyLeads::class,
        Commands\CheckRoistatAnalyticsDimensionsValues::class,
        Commands\CheckRoistatAnalytic::class,
        Commands\NotifyCompanies::class,
        Commands\CheckRoistatGoogleAnalytic::class,
        Commands\CheckRoistatTransactions::class,
        Commands\GetRecentLeads::class,
        Commands\CountMonthCompnanyCosts::class,
        Commands\MailNotifyMain::class,
        Commands\CompaniesYesterdayAnalytics::class,
        Commands\CompaniesWeekAnalytics::class,
        Commands\CompaniesAvitoAnalytics::class,
        Commands\CountWeekLeads::class,
        Commands\AccountStatement::class,
        Commands\RemindToCallback::class,
        Commands\Moderations::class,
        Commands\ModerationsAdmin::class,
        Commands\CheckAnalytic::class,
        SitePagespeedCommand::class,
        SitesPagespeedSyncCommand::class,
        ScheduleList::class,
        Commands\UpdateCompanyReport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('yandex:checkBalance')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('companies:analytic-yesterday')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('roistat:googleAnalytic')
            ->dailyAt('6:00')
            ->withoutOverlapping();

        $schedule->command('roistat:statistic')
            ->dailyAt('6:01')
            ->withoutOverlapping();

        $schedule->command('roistat:proxyLeads')
            ->dailyAt('6:02')
            ->withoutOverlapping();

        $schedule->command('roistat:transactions')
            ->dailyAt('6:03')
            ->withoutOverlapping();

        $schedule->command('companies:analytic-avito')
            ->dailyAt('6:04')
            ->withoutOverlapping();

        $schedule->command('mailNotifications:notify')
            ->dailyAt('6:31')
            ->description('Send notifications about problems in companies')
            ->before(function () {
                Log::info('MailNotifications:notify command was successful started');
            })
            ->after(function () {
                Log::info('MailNotifications:notify command was successful finished');
            })
            ->withoutOverlapping();

        $schedule->command('mail:notify-main')
            ->dailyAt('10:00')
            ->withoutOverlapping();

        $schedule->command('roistat:googleAnalytic')
            ->dailyAt('12:00')
            ->withoutOverlapping();

        $schedule->command('companies:analytic-avito')
            ->dailyAt('12:00')
            ->withoutOverlapping();

        $schedule->command('roistat:googleAnalytic')
            ->dailyAt('18:00')
            ->withoutOverlapping();

        $schedule->command('companies:analytic-avito')
            ->dailyAt('18:00')
            ->withoutOverlapping();

        $schedule->command('companies:analytic-week')
            ->dailyAt('22:00')
            ->withoutOverlapping();

        $schedule->command('roistat:analyticsDimensionsValues')
            ->weekly();

        $schedule->command('report:auto-approve')
            ->monthlyOn(1, '1:00');

        $schedule->command('finance:generate')
            ->monthlyOn(1, '2:00');

        $schedule->command('beget:backup-database')
            ->daily()
            ->description('Backup database on beget account');

        $schedule->command('moderations:send_moderations_admin')
            ->dailyAt('4:00')
            ->withoutOverlapping();

        //every minute
        $schedule->command('tinkoff:account-statement')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->command('remind-to-callback')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('moderations:send_moderations')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('check:countJobs')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('knam:import')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('report:update')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->call(function () {
            \DB::table('schedule_task_logs')
                ->where('created_at', '<', Carbon::now()->subMonth()->toDateTimeString())
                ->delete();
        })->everyMinute();

        $schedule->command('companies:hide-leads-if-low-balance')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        (new Notifier($schedule))->register();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
