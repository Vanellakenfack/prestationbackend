<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('admin/login', [AdminController::class, 'login']);
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
      // Réservations
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/prestataires/{prestataire}/disponibilites', [BookingController::class, 'getDisponibilites']);
    //Évaluations
    Route::post('/services/{service}/reviews', [ReviewController::class, 'store']);
    // Admin routes (protégées)
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::post('create', [AdminController::class, 'createAdmin']);
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('/services', [AdminController::class, 'listServices']);
        Route::post('/services/{service}/moderate', [AdminController::class, 'moderateService']);
            Route::get('/stats', [AdminController::class, 'stats']);
        });
    });