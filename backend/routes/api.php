<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SignalementPublicController;
use App\Http\Controllers\AdminPreuveController;
use App\Http\Controllers\AdminSignalementController;
use App\Http\Controllers\AdminStatController;
use App\Http\Controllers\AdminAgentController;
use Illuminate\Support\Facades\Route;

// Chaque limite a son préfixe : sans lui, Laravel compte toutes les routes
// « throttle:N,M » dans un seul compteur par adresse IP, et quelques
// consultations de suivi suffisent à bloquer la connexion d'un agent qui
// partage la même IP (bureau, réseau mobile).

// Auth
Route::middleware('throttle:5,1,connexion')->post('/auth/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'actif'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// Public
Route::middleware('throttle:10,1,depot')->post('/signalements', [SignalementPublicController::class, 'store']);
Route::middleware('throttle:30,1,suivi')->get('/signalements/{code}/statut', [SignalementPublicController::class, 'statut']);
Route::middleware('throttle:10,1,preuves')->post('/signalements/{code}/preuves', [SignalementPublicController::class, 'storePreuves']);
Route::get('/stats/publiques', [AdminStatController::class, 'publiques']);

// Admin (auth)
Route::middleware(['auth:sanctum', 'actif'])->prefix('admin')->group(function () {
    Route::get('/signalements', [AdminSignalementController::class, 'index']);
    Route::get('/signalements/{id}', [AdminSignalementController::class, 'show']);
    Route::patch('/signalements/{id}', [AdminSignalementController::class, 'update']);
    // scopeBindings : une preuve d'un autre dossier renvoie 404, la protection
    // ne repose pas sur le seul identifiant de la preuve.
    Route::get('/signalements/{signalement}/preuves/{preuve}', [AdminPreuveController::class, 'show'])
        ->scopeBindings();
    Route::get('/stats', [AdminStatController::class, 'admin']);
    Route::get('/agents', [AdminAgentController::class, 'index'])->middleware('superviseur');
});
