<?php

use App\Cabinet\Dashboard\Controllers\IndexController;

Route::group(
    ['middleware' => ['role:admin|managers|super-admin']],
    function () {
        Route::get('/dashboard', [IndexController::class, 'dashboard'])
            ->middleware('auth')
            ->name('dashboard');
    }
);
