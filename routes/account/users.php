<?php

use App\Cabinet\User\Controllers\UserController;

Route::group(
    ['middleware' => ['role:admin|super-admin']],
    function () {
        Route::resource('users', UserController::class);
    }
);
