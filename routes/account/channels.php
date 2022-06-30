<?php

use App\Cabinet\Channel\Controllers\ChannelsController;

Route::group(
    ['middleware' => ['role:admin|super-admin']],
    function () {
        Route::post('channels/update', [ChannelsController::class, 'update']);
        Route::resource('channels', ChannelsController::class);
    }
);
