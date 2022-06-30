<?php

namespace App\Providers;

use App\Domain\Bitrix\Listeners\BitrixListener;
use App\Domain\Company\Events\StoreReconciliationEvent;
use App\Domain\Company\Listeners\Reconciliation\CreateReconciliationListener;
use App\Domain\Company\Listeners\Reconciliation\FinanceReportRegenerateListener;
use App\Domain\Notification\Listeners\LogSentMailListener;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Events\StatusProxyLeadEvent;
use App\Domain\ProxyLead\Events\WrongProxyLeadPayloadEvent;
use App\Domain\ProxyLead\Listeners\SendProxyLeadListener;
use App\Domain\ProxyLead\Listeners\SendWrongProxyLeadListener;
use App\Domain\ProxyLead\Listeners\StatusProxyLeadListener;
use App\Domain\Tinkoff\Events\TinkoffApiSent;
use App\Domain\Tinkoff\Listeners\LogTinkoffApiListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        StatusProxyLeadEvent::class => [
            StatusProxyLeadListener::class
        ],
        StoreReconciliationEvent::class => [
            CreateReconciliationListener::class,
            FinanceReportRegenerateListener::class,
        ],
        \App\Domain\Company\Events\CompaniesAnalyticsReceived::class => [
            \App\Domain\Company\Listeners\UpdateCompaniesCosts::class,
        ],
        CreateProxyLeadEvent::class => [
            SendProxyLeadListener::class,
            BitrixListener::class,
        ],
        UpdateYclientsPrimaryCompany::class => [
            SetYclientsWebhookListener::class,
        ],
        WrongProxyLeadPayloadEvent::class => [
            SendWrongProxyLeadListener::class,
        ],
        MessageSent::class => [
            LogSentMailListener::class,
        ],
        TinkoffApiSent::class => [
            LogTinkoffApiListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

        //
    }
}
