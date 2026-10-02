<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /* ─────── Public Routes (بدون مصادقة) ─────── */

    // Health Check
    Route::get('/health', fn() => response()->json([
        'status'  => 'ok',
        'message' => 'API يعمل بنجاح',
        'time'    => now()->toIso8601String(),
    ]));

    // المنتجات
    Route::get('/products',          [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}',   [ProductController::class, 'show']);

    // الأقسام
    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    // السلة (تعمل للزوار والمستخدمين)
    Route::prefix('cart')->group(function () {
        Route::get('/',              [CartController::class, 'show']);
        Route::post('/items',        [CartController::class, 'addItem']);
        Route::patch('/items/{id}',  [CartController::class, 'updateItem']);
        Route::delete('/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/',           [CartController::class, 'clear']);
    });

    // المصادقة
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);

        // Routes تتطلب مصادقة
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me',     [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });
});
