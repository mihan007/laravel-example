<?php

use App\Cabinet\Dashboard\Controllers\LeadgenChartController;

Route::group(
    ['middleware' => ['manager']],
    function () {
        Route::get('leadgen-chart', [LeadgenChartController::class, 'index'])
            ->name('leadgen.index');
    }
);
