<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes pour l'API SaaS de rédaction assistée par IA.
| Authentification via Laravel Sanctum.
|
*/

// ===========================
// Routes publiques (sans auth)
// ===========================
Route::prefix('auth')->group(function () {
    // POST /api/auth/register - Inscription
    Route::post('/register', [AuthController::class, 'register']);

    // POST /api/auth/login - Connexion
    Route::post('/login', [AuthController::class, 'login']);
});

// ===========================
// Routes protégées (auth:sanctum)
// ===========================
Route::middleware('auth:sanctum')->group(function () {

    // --- Routes d'authentification ---
    Route::prefix('auth')->group(function () {
        // POST /api/auth/logout - Déconnexion
        Route::post('/logout', [AuthController::class, 'logout']);

        // GET /api/auth/me - Profil utilisateur
        Route::get('/me', [AuthController::class, 'me']);
    });

    // --- Routes IA ---
    Route::prefix('ai')->group(function () {
        // POST /api/ai/generate-text - Génération de texte
        Route::post('/generate-text', [AIController::class, 'generateText']);

        // POST /api/ai/summarize - Résumé de texte
        Route::post('/summarize', [AIController::class, 'summarize']);

        // POST /api/ai/rewrite - Réécriture de texte
        Route::post('/rewrite', [AIController::class, 'rewrite']);

        // POST /api/ai/questions - Génération de questions
        Route::post('/questions', [AIController::class, 'questions']);

        // GET /api/ai/history - Historique des requêtes
        Route::get('/history', [AIController::class, 'history']);

        // GET /api/ai/request/{id} - Détail d'une requête
        Route::get('/request/{id}', [AIController::class, 'show']);
    });
});

// Route de health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});
