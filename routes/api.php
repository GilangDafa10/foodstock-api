<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/stock/in', [StockController::class, 'stockIn']);
    Route::post('/stock/out', [StockController::class, 'stockOut']);
});
