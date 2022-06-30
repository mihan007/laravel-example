<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Api\Support\Controllers\RequestLog;
use App\Cabinet\Account\Controllers\AccountController;
use App\Cabinet\Amocrm\Controllers\AmocrmWebhook;
use App\Cabinet\Client\Subscription\Controllers\SubscriptionController;
use App\Cabinet\Dashboard\Controllers\IndexController;
use App\Cabinet\Finance\Controllers\FinanceController as StaffFinanceController;
use App\Cabinet\Finance\Controllers\ListController as StaffListController;
use App\Cabinet\Finance\Controllers\PaymentController;
use App\Cabinet\ProxyLead\Controllers\LidogeneratorSubscriptionController;
use App\Cabinet\Support\Controllers\SchedulePingController;
use App\Cabinet\User\Controllers\Auth\RegisterController;
use App\Cabinet\User\Controllers\RolesController;
use App\Cabinet\Vk\Controllers\ConfirmedController;
use App\Cabinet\Vk\Controllers\InstructionsController;
use App\Cabinet\Vk\Controllers\KeyGeneratorController;
use App\Cabinet\YandexDirect\Controllers\YandexDirectController;
use App\Cabinet\YooMoney\Controllers\YandexSettingsController;

Route::namespace('App\Cabinet\User\Controllers')
    ->group(
        function () {
            Auth::routes();
        }
    );

Route::group(
    [
        'middleware' => [
            'auth',
            'role:super-admin',
        ],
    ],
    function () {
        Route::get('accounts/ajaxList', [AccountController::class, 'ajaxList'])->name('accounts.ajaxList');
        Route::resource('accounts', AccountController::class);
    }
);

Route::get(
    '/home',
    function () {
        return redirect('/', 301);
    }
);

Route::middleware('auth')
    ->group(
        function () {
            Route::get('/', [IndexController::class, 'index']);
        }
    );

Route::name('account.')
    ->prefix('account/{accountId}')
    ->middleware(['auth', 'account'])
    ->group(
        function () {
            require __DIR__ . '/account/companies.php';
        }
    );

Route::name('account.')
    ->prefix('account/{accountId}')
    ->middleware(['auth', 'account', 'role:super-admin|admin|managers'])
    ->group(
        function () {
            require __DIR__ . '/account/dashboard.php';
            require __DIR__ . '/account/users.php';
            require __DIR__ . '/account/channels.php';
            require __DIR__ . '/account/settings.php';
            require __DIR__ . '/account/ajax.php';
        }
    );

Route::name('user.')
    ->prefix('/user/{company:public_id}')
    ->middleware(['auth', 'is.company.user', 'is.company.manager'])
    ->group(
        function () {
            require __DIR__ . '/user.php';
        }
    );

Route::resource('/schedule/ping', SchedulePingController::class)
    ->only('index');
Route::any('/amocrm/webhook', [AmocrmWebhook::class, 'index']);

Route::get('/yandex_direct/token', [YandexDirectController::class, 'create']);
Route::post('/yandex_direct/token', [YandexDirectController::class, 'store'])
    ->name('pages.yandex-direct-parser');

Route::get('/lidogenerator/subscription', [LidogeneratorSubscriptionController::class, 'store']);

Route::group(
    ['middleware' => ['role:admin|managers|super-admin', 'auth', 'manager']],
    function () {
        Route::get('/get_half_year_leads', [IndexController::class, 'getHalfYearLeads']);
        Route::get('/get_month_leads', [IndexController::class, 'getMonthLeads']);

        Route::resource('instructions', InstructionsController::class);

        Route::match(['get', 'post'], '/request/log', [RequestLog::class, 'show']);
    }
);

Route::group(
    ['middleware' => ['auth', 'role:super-admin']],
    function () {
        /* admin roles*/
        Route::get('roles', [RolesController::class, 'index']);
        Route::post('roles', [RolesController::class, 'save']);
        Route::get('roles/{id}/edit', [RolesController::class, 'edit']);
        Route::post('roles/update', [RolesController::class, 'update']);
        Route::delete('roles/{id}', [RolesController::class, 'delete']);
        Route::post('accounts/update', [AccountController::class, 'update']);
        Route::resource('accounts', AccountController::class)
            ->only('create', 'store', 'edit', 'update', 'destroy');
    }
);

Route::middleware(['auth', 'role:admin|super-admin'])
    ->name('finance.')
    ->prefix('finance')
    ->group(
        function () {
            Route::resource('/', StaffFinanceController::class)->only(['index']);
            Route::resource('payment', PaymentController::class)->only(['store', 'destroy']);
        }
    );

Route::get('/vk/key-generator', [KeyGeneratorController::class, 'index']);
Route::post('/vk/key-generator', [KeyGeneratorController::class, 'store']);
Route::get('/vk/key-generator/confirmed', [ConfirmedController::class, 'index']);
Route::post('/vk/key-generator/confirmed', [ConfirmedController::class, 'store']);

Route::get('user/activation/{token}', [RegisterController::class, 'activateUser'])
    ->name('user.activate');

Route::get(
    '/confirmation',
    function () {
        return view('auth.confirmation');
    }
)->middleware('guest');

Route::name('subscription.')->group(
    function () {
        Route::get(
            'ajax-unsubscribe-data/{unsubscribe_type}/{key}',
            [SubscriptionController::class, 'ajaxGetUnsubscribeDate']
        )
            ->name('ajax.unsubscribe.data');
        Route::get('unsubscribe/{key}', [SubscriptionController::class, 'unsubscribe'])
            ->name('unsubscribe.one');
        Route::get('unsubscribe/all/{key}', [SubscriptionController::class, 'unsubscribeAll'])
            ->name('unsubscribe.all');
        Route::get('ajax-unsubscribe/{key}', [SubscriptionController::class, 'ajaxUnsubscribe'])
            ->name('ajax.unsubscribe.one');
        Route::get('ajax-unsubscribe/all/{key}', [SubscriptionController::class, 'ajaxUnsubscribeAll'])
            ->name('ajax.unsubscribe.all');
        Route::get('subscribe/all/pending/{key}', [SubscriptionController::class, 'subscribeAllPending'])
            ->name('subscribe.pending');
        Route::get('manage/{key}', [SubscriptionController::class, 'manage'])
            ->name('manage');
        Route::post('manage/{key}', [SubscriptionController::class, 'manage']);
        Route::get('subscription-admin/{key}', [SubscriptionController::class, 'manageCompany'])
            ->name('company.admin');
        Route::post('subscription-admin/{key}', [SubscriptionController::class, 'saveCompany']);
        Route::post('change-admin/{key}', [SubscriptionController::class, 'changeAdmin'])
            ->name('company.changeAdmin');
    }
);

Route::any('yandex/webhook/{id}', [YandexSettingsController::class, 'processWebhook'])
    ->name('yandex.webhook');

Route::get(
    'storage/{filename}',
    function ($filename) {
        $path = storage_path('app/public/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);

        return $response;
    }
);
