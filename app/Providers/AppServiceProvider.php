<?php

namespace App\Providers;

use App\Domain\Notification\Channels\MailChannel;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Observers\PlReportLeadObserver;
use App\Models\Account;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->addSharedViewVariables();
        PlReportLead::observe(PlReportLeadObserver::class);
        $this->registerBladeDirectives();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function () {
            return base_path('public_html');
        });
        $this->app->bind(\Illuminate\Notifications\Channels\MailChannel::class, MailChannel::class);
    }

    private function addSharedViewVariables()
    {
        \View::share('showSaveButton', false);
    }

    private function registerBladeDirectives()
    {
        Blade::directive('staff', function () {
            return "<?php if (current_user_is_staff()) { ?>";
        });

        Blade::directive('endStaff', function () {
            return "<?php } ?>";
        });

        Blade::directive('elseStaff', function () {
            return "<?php } else { ?>";
        });

        Blade::directive('client', function () {
            return "<?php if (current_user_is_client()) { ?>";
        });

        Blade::directive('elseClient', function () {
            return "<?php } else { ?>";
        });

        Blade::directive('endClient', function () {
            return "<?php } ?>";
        });
    }
}
