<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SignalementPublicController;
use App\Http\Controllers\AdminSignalementController;
use App\Http\Controllers\AdminStatController;
use App\Http\Controllers\AdminAgentController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// Public
Route::post('/signalements', [SignalementPublicController::class, 'store']);
Route::get('/signalements/{code}/statut', [SignalementPublicController::class, 'statut']);
Route::post('/signalements/{code}/preuves', [SignalementPublicController::class, 'storePreuves']);
Route::get('/stats/publiques', [AdminStatController::class, 'publiques']);

// Admin (auth)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/signalements', [AdminSignalementController::class, 'index']);
    Route::get('/signalements/{id}', [AdminSignalementController::class, 'show']);
    Route::patch('/signalements/{id}', [AdminSignalementController::class, 'update']);
    Route::get('/stats', [AdminStatController::class, 'admin']);
    Route::get('/agents', [AdminAgentController::class, 'index']);
});
