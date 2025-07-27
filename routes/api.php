<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController; // Assurez-vous d'importer CategoryController

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================================================
// Routes PUBLIQUES (accessibles sans authentification)
// ========================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('admin/login', [AdminController::class, 'login']);

// Routes pour les catégories et les services (doivent être publiques pour la page d'accueil)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']); // Pour la recherche publique de services

// ========================================================================
// Routes PROTÉGÉES (nécessitent une authentification via Sanctum)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });

    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['prestataire', 'client']);
    });

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Services du prestataire (gestion CRUD des services par le prestataire)
    Route::prefix('services')->group(function () {
        // Route::get('/', [ServiceController::class, 'index']); // Cette route est déjà publique ci-dessus
        Route::post('/', [ServiceController::class, 'store']);
        Route::get('/{service}', [ServiceController::class, 'show']);
        Route::put('/{service}', [ServiceController::class, 'update']);
        Route::delete('/{service}', [ServiceController::class, 'destroy']);
        Route::get('/prestataire/{prestataireId}', [ServiceController::class, 'byPrestataire']);
    });

    // Réservations
    Route::prefix('bookings')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{booking}', [BookingController::class, 'show']);
        Route::put('/{booking}', [BookingController::class, 'update']);
        Route::delete('/{booking}', [BookingController::class, 'destroy']);
        Route::get('/user/{userId}', [BookingController::class, 'byUser']);
        Route::get('/prestataire/{prestataireId}', [BookingController::class, 'byPrestataire']);
    });

    // Avis
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('/{review}', [ReviewController::class, 'show']);
        Route::put('/{review}', [ReviewController::class, 'update']);
        Route::delete('/{review}', [ReviewController::class, 'destroy']);
        Route::get('/service/{serviceId}', [ReviewController::class, 'byService']);
        Route::get('/prestataire/{prestataireId}', [ReviewController::class, 'byPrestataire']);
    });

    // Administration
    Route::prefix('admin')->middleware('can:manage-admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::post('create', [AdminController::class, 'createAdmin']);
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('/services', [AdminController::class, 'listServices']);
        Route::get('stats', [AdminController::class, 'stats']);
        Route::put('services/{id}/approve', [AdminController::class, 'approveService']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
    });

    // Disponibilités (gestion par le prestataire)
    // Récupérer les disponibilités pour un prestataire sur une période donnée
    Route::get('/availabilities/{prestataireId}', [AvailabilityController::class, 'index']);
    // Mettre à jour ou créer des disponibilités (peut prendre un tableau de jours/créneaux)
    Route::post('/availabilities/{prestataireId}/batch-update', [AvailabilityController::class, 'batchUpdate']);
});

// Vous pourriez aussi avoir des routes pour un seul jour si nécessaire:
// Route::put('/availabilities/{id}', [AvailabilityController::class, 'update']);
