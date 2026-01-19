<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// callback biasanya PUBLIC
Route::post('/payment/callback', [PaymentController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    // Route Khusus Admin
    Route::middleware(['role:admin'])->group(function () {});

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/stock/in', [StockController::class, 'stockIn']);
    Route::post('/stock/out', [StockController::class, 'stockOut']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/payments', [PaymentController::class, 'store']);
});
