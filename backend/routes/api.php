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

// DEBUG ROUTE - À SUPPRIMER
Route::post('/debug-json', function(\Illuminate\Http\Request $req) {
    return response()->json([
        'isJson' => $req->isJson(),
        'content_type' => $req->header('CONTENT_TYPE'),
        'input_email' => $req->input('email'),
        'json_all' => $req->json()->all(),
        'content_raw' => $req->getContent(),
        'post' => $_POST,
    ]);
});

// DEBUG ROUTE 2
Route::post('/debug-login', function(\Illuminate\Http\Request $req) {
    try {
        $req->validate(['email' => 'required|email', 'password' => 'required']);
        return response()->json(['status' => 'validate_ok', 'email' => $req->input('email')]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['status' => 'validate_fail', 'errors' => $e->errors(), 'all' => $req->all(), 'json_all' => $req->json()->all()]);
    }
});

// DEBUG ROUTE 3 - simulate AuthController
Route::post('/debug-auth', [\App\Http\Controllers\AuthController::class, 'debugLogin']);
