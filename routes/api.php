<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Services
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::get('/{service}', [ServiceController::class, 'show']);
        Route::put('/{service}', [ServiceController::class, 'update']);
        Route::delete('/{service}', [ServiceController::class, 'destroy']);
        Route::get('/prestataire/{prestataireId}', [ServiceController::class, 'byPrestataire']);
    });

    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['prestataire', 'client']);
    });

    // Admin routes (si besoin)
    Route::middleware('role:admin')->group(function () {
        // Route admin ici
    });
});