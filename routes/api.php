<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/',         [WalletController::class, 'balance']);
        Route::post('/topup',   [WalletController::class, 'topUp']);
        Route::post('/transfer',[WalletController::class, 'transfer']);
    });

    // Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/',          [TransactionController::class, 'index']);
        Route::get('/{reference_code}', [TransactionController::class, 'show']);
    });

});