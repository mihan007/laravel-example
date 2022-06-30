<?php

use App\Cabinet\Account\Controllers\SettingsController;
use App\Cabinet\Account\Controllers\TinkoffSettingsController;
use App\Cabinet\User\Controllers\UserController;

Route::group(
    ['middleware' => ['role:admin|super-admin']],
    function () {
        //Users
        Route::resource('users', UserController::class)->except('index');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

        Route::post('users/update', [UserController::class, 'update']);

        Route::post('settings', [SettingsController::class, 'save']);

        Route::post('tinkoff_settings_check', [TinkoffSettingsController::class, 'check'])
            ->name('tinkoff_settings_check');
    }
);
