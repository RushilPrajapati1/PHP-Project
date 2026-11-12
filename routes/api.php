<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Names are prefixed with `api.` so they don't collide with the web (Inertia) routes.
Route::middleware('auth:sanctum')->name('api.')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('accounts.transactions', TransactionController::class)->shallow();
    Route::get('accounts/{account}/summary', SummaryController::class)->name('accounts.summary'); // ?month=2026-10
});
