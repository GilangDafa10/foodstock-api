<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Admin\StockController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\UserAddressController;

// Public Routes API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);


// callback biasanya PUBLIC
Route::post('/payment/midtrans/callback', [PaymentController::class, 'callback']);

Route::middleware('auth:sanctum', 'sanctum.idle')->group(function () {
    // Route Khusus Admin
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Product CRUD
        // Route::apiResource('/admin/products', ProductController::class);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        // Category CRUD
        Route::apiResource('/categories', CategoryController::class);
        // Kontrol Stok
        Route::post('/stock/adjust', [StockController::class, 'adjust']);
        Route::get('/stock/history', [StockController::class, 'history']);

        // Manajemen Order
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::delete('/orders/{order}/cancel', [AdminOrderController::class, 'cancel']);
    });

    // Route Khusus Customer
    Route::middleware(['role:customer'])->group(function () {
        Route::apiResource('/addresses', UserAddressController::class);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::post('/payments/{orderId}/retry', [PaymentController::class, 'retry']);
});
