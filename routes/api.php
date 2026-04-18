<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout',         [AuthController::class, 'logout']);
    Route::get('/profile',              [ProfileController::class, 'show']);
    Route::put('/profile',              [ProfileController::class, 'update']);
    Route::put('/profile/password',     [ProfileController::class, 'updatePassword']);
});