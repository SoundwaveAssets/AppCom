<?php

use App\Http\Controllers\Api\AdminBrandController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.firebase.optional')->group(function () {
    Route::get('/catalog/products', [CatalogController::class, 'index']);
    Route::get('/catalog/products/{product}', [CatalogController::class, 'show']);

    Route::post('/checkout/validate-cart', [CheckoutController::class, 'validateCart']);
    Route::post('/checkout/payment-intent', [CheckoutController::class, 'createPaymentIntent']);
});

Route::post('/webhooks/stripe', [WebhookController::class, 'handle'])->name('webhooks.stripe');

Route::middleware('auth.firebase')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);

    Route::prefix('/admin')->middleware('role:admin')->group(function () {
        Route::apiResource('categories', AdminCategoryController::class)->except(['destroy']);
        Route::patch('categories/{category}/toggle', [AdminCategoryController::class, 'toggleActive']);

        Route::apiResource('brands', AdminBrandController::class)->except(['destroy']);
        Route::patch('brands/{brand}/toggle', [AdminBrandController::class, 'toggleActive']);

        Route::apiResource('products', AdminProductController::class);
        Route::patch('products/{product}/stock', [AdminProductController::class, 'updateStock']);

        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);
    });
});