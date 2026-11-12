<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::resource('accounts.transactions', TransactionController::class)->only(['store', 'destroy'])->shallow();
});

require __DIR__.'/settings.php';
