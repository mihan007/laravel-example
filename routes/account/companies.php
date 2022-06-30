<?php

use App\Api\Company\Controllers\CompanyController;
use App\Cabinet\Bitrix\Controllers\BitrixController;
use App\Cabinet\Bitrix\Controllers\CRMIntegrationController;
use App\Cabinet\Company\Controllers\CompaniesController;
use App\Cabinet\Company\Controllers\CompanyAnalyticCalculatorController;
use App\Cabinet\Company\Controllers\CompanyNotificationController;
use App\Cabinet\Company\Controllers\CompanyReplacementController;
use App\Cabinet\Company\Controllers\CompanyReportController;
use App\Cabinet\Dashboard\Controllers\SiteController;
use App\Cabinet\Finance\Controllers\BalanceController;
use App\Cabinet\Finance\Controllers\FinanceController;
use App\Cabinet\ProxyLead\Controllers\ApproveController;
use App\Cabinet\ProxyLead\Controllers\EmailableController;
use App\Cabinet\ProxyLead\Controllers\ProxyLeadSettingsController;
use App\Cabinet\ProxyLead\Controllers\ProxyLeadsReportController;
use App\Cabinet\ProxyLead\Controllers\ReconciliationController;
use App\Cabinet\YandexDirect\Controllers\YandexDirectController;

Route::prefix('companies')->middleware(['is.account.company', 'is.company.user'])->group(
    function () {
        Route::get('{id}/destroy', [CompaniesController::class, 'destroy'])->name('companies.delete');
        Route::post('update/{id}', [CompaniesController::class, 'update'])->name('companies.update');
        Route::get('ajaxList', [CompaniesController::class, 'ajaxList'])->name('companies.ajaxList');
        Route::get('ajaxShow', [CompaniesController::class, 'ajaxShow'])->name('companies.ajaxShow');
        Route::get('ajaxUpdate', [CompaniesController::class, 'ajaxUpdate'])->name('companies.ajaxUpdate');
        Route::post('{id}/status-notifications', [CompaniesController::class, 'ajaxNotifications']);

        Route::resource('{id}/notification', CompanyNotificationController::class);
    }
);

Route::resource('companies', CompaniesController::class)
    ->middleware(['is.staff', 'is.account.company']);

Route::prefix('company')
    ->name('company.')
    ->middleware(['is.account.company', 'is.company.manager', 'is.company.user'])
    ->group(
        function () {
            Route::get('{id}', [CompaniesController::class, 'show'])
                ->name('show');

            Route::get('/yandex_direct/confirmation/{id?}', [YandexDirectController::class, 'show'])
                ->name('yandex_direct-confirmation');

            Route::post(
                '/companies/ajax_yandex_balance_for_period/{id}',
                [CompaniesController::class, 'ajaxYandexBalanceForPeriod']
            )->name('ajax_yandex_balance_for_period');

            Route::get('/companies/{id}/report', [CompanyReportController::class, 'index'])->name('report.index');
            Route::get('/companies/{id}/report/edit', [CompanyReportController::class, 'edit'])->name('report.edit');
            Route::get('/companies/{id}/report/verify', [CompanyReportController::class, 'verify'])->name(
                'report.verify'
            );
            Route::get('/companies/{id}/report/approve', [CompanyReportController::class, 'approve'])->name(
                'report.approve'
            );
            Route::put('/companies/{id}/report', [CompanyReportController::class, 'update'])->name('report.update');
            Route::post('/companies/{id}/report', [CompanyReportController::class, 'ajaxUpdate'])->name(
                'report.ajaxupdate'
            );

            Route::get('/companies/{id}/replacement', [CompanyReplacementController::class, 'index'])->name(
                'replacement.index'
            );
            Route::post('/companies/{id}/replacement/store', [CompanyReplacementController::class, 'store'])->name(
                'replacement.store'
            );
            Route::put('/companies/{id}/replacement', [CompanyReplacementController::class, 'update'])->name(
                'replacement.update'
            );

            Route::get('/companies/{id}/sites', [SiteController::class, 'index'])->name('sites');

            Route::any(
                '/companies/{id}/analytic_calculator',
                [CompanyAnalyticCalculatorController::class, 'index']
            )->name('analytic_calculator');
        }
    );

Route::middleware(['is.company.user'])
    ->name('company.')
    ->group(
        function () {
            Route::post(
                '/companies/{company}/proxy-leads/{proxyLead}/update',
                [ProxyLeadSettingsController::class, 'update']
            )->name('proxy-lead.update');

            Route::resource(
                '/companies/{company}/proxy-leads',
                ProxyLeadSettingsController::class,
                [
                    'only' => [
                        'index',
                        'store',
                    ],
                ]
            );

            Route::resource('/companies/{company}/proxy-leads/bitrix', BitrixController::class, ['only' => 'store']);

            Route::get('/companies/{company}/crm-integration', [CRMIntegrationController::class, 'index'])->name(
                'crm-integration.index'
            );

            Route::resource('/companies/{company}/proxy-leads/bitrix', BitrixController::class, ['only' => 'store']);

            Route::get('/companies/{company}/proxy-leads', [ProxyLeadsReportController::class, 'index'])
                ->name('proxy-leads');

            Route::get('/companies/{company}/proxy-leads/excel', [ProxyLeadsReportController::class, 'excel'])
                ->name('proxy-leads.excel');

            Route::get('/companies/{company}/finance', [FinanceController::class, 'index'])
                ->name('finance');

            Route::get('/companies/{company}/finance/ajaxList', [FinanceController::class, 'ajaxList'])
                ->name('finance.ajaxList');

            Route::get('/companies/{company}/balance', [FinanceController::class, 'balanceCompany'])
                ->name('balance');

            Route::get('/companies/{company}/expenseIncome', [FinanceController::class, 'expenseIncome'])
                ->name('expense-income');

            Route::post('/companies/{company}/markInvoice', [BalanceController::class, 'markInvoice'])
                ->name('mark-invoice');

            Route::post('/companies/{company}/replenishBalance', [BalanceController::class, 'replenish'])
                ->name('replenish-balance');

            Route::get(
                '/companies/{company}/proxy-leads/report/approve',
                [ProxyLeadsReportController::class, 'approve']
            )
                ->name('proxy-lead.report.approve');
            Route::get(
                '/companies/{company}/proxy-leads/report/confirm',
                [ProxyLeadsReportController::class, 'confirm']
            )
                ->name('proxy-lead.report.confirm');
            Route::get('/companies/{company}/proxy-leads/report/edit', [ProxyLeadsReportController::class, 'edit'])
                ->name('proxy-lead.report.edit');
            Route::match(
                ['post', 'delete'],
                '/companies/{company}/proxy-leads/report/{lead}/delete',
                [ProxyLeadsReportController::class, 'delete']
            )->name('proxy-lead.report.delete');
            Route::match(
                ['post', 'put', 'patch'],
                '/proxy-leads/{lead}/update',
                [ProxyLeadsReportController::class, 'update']
            )->name('proxy-lead.update');

            Route::match(
                ['post', 'put', 'patch'],
                '/companies/{company}/proxy-leads/{lead}/updateClient',
                [ProxyLeadsReportController::class, 'updateClient']
            )->name('proxy-lead.updateClient');

            // Emailable
            Route::get(
                '/companies/{company}/proxy-leads/report/emailable',
                [EmailableController::class, 'index']
            )->name('proxy-lead.report.emailable');

            Route::match(
                ['put', 'patch'],
                '/companies/{company}/proxy-leads/report/emailable/{proxyLead}/update',
                [EmailableController::class, 'update']
            )->name('proxy-lead.report.emailable.update');

            Route::delete(
                '/companies/{company}/proxy-leads/emailable/{proxyLead}',
                [EmailableController::class, 'destroy']
            )->name('proxy-lead.report.emailable.destroy');

            // Approve
            Route::get(
                '/companies/{company}/proxy-leads/report/approve/{period}',
                [ApproveController::class, 'show']
            );
            Route::post(
                '/companies/{company}/proxy-leads/report/approvenew',
                [ApproveController::class, 'store']
            )->name('proxy-lead.report.approvenew');

            // Reconciliation
            Route::get(
                '/companies/{company}/proxy-leads/report/reconciliation/{period}',
                [ReconciliationController::class, 'show']
            )->name('reconciliation.show');

            Route::post(
                '/companies/{company}/proxy-leads/report/reconciliation',
                [ReconciliationController::class, 'store']
            )->name('reconciliation.store');

            Route::post(
                '/companies/change/amount_limit',
                [CompanyController::class, 'changeLimit']
            )->name('changeLimit');
        }
    );
