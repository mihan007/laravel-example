<?php

// user
use App\Cabinet\Finance\Controllers\BalanceController;
use App\Cabinet\Finance\Controllers\FinanceController;

Route::get('/finance', [FinanceController::class, 'redirectToAccountCompany'])
    ->name('finance');

Route::get('/invoice/{paymentTransaction}/{download?}', [BalanceController::class, 'invoice'])
    ->name('invoicePdf');
