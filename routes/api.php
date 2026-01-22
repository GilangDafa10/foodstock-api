<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;

// Public Routes API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// callback biasanya PUBLIC
Route::post('/payment/callback', [PaymentController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    // Route Khusus Admin
    Route::middleware(['role:admin'])->group(function () {
        // Product CRUD
        // Route::apiResource('/admin/products', ProductController::class);
        Route::post('/admin/products', [ProductController::class, 'store']);
        Route::put('/admin/products/{product}', [ProductController::class, 'update']);
        Route::delete('/admin/products/{product}', [ProductController::class, 'destroy']);
        // Category CRUD
        Route::apiResource('/admin/categories', CategoryController::class);


        Route::get('/admin/orders', [AdminOrderController::class, 'index']);
        Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
    });

    // Route Khusus Admin
    Route::middleware(['role:customer'])->group(function () {});

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/stock/in', [StockController::class, 'stockIn']);
    Route::post('/stock/out', [StockController::class, 'stockOut']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/payments', [PaymentController::class, 'store']);
});
