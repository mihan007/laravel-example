<?php

use App\Api\ProxyLead\Controllers\WebHookControllerCommon;
use App\Api\Support\Controllers\RequestLog;
use App\Cabinet\Account\Controllers\AccountController;
use App\Cabinet\User\Controllers\UserController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->namespace('Api')->name('api.v1.')->group(function () {
    Route::match(['get', 'post'], '/companies/proxy-leads/store', [WebHookControllerCommon::class, 'store'])
        ->name('company.proxy-lead.store');

    Route::match(['get', 'post'], '/web-leads/store/{key}', [WebHookControllerCommon::class, 'store'])
        ->name('web-leads.store');

    Route::match(['get', 'post'], '/web-leads/webhook/{key}', [WebHookControllerCommon::class, 'store'])
        ->name('web-leads.common.store');

    Route::match(['get', 'post'], '/request/log', [RequestLog::class, 'log'])
        ->name('request.log');

    Route::get('/accounts/{account}', [AccountController::class, 'edit'])
        ->name('accounts.edit');

    Route::get('/users/admins', [UserController::class, 'admins'])
        ->name('users.admins');
});

Route::post('/notification/yandex', function () {
    throw new Exception('Route /notification/yandex is deprecated');
})->name('payment.notification.yandex');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
