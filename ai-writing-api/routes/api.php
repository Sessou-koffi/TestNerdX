<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Routes IA
    Route::prefix('ai')->group(function () {
        Route::post('/generate-text', [AIController::class, 'generateText']);
        Route::post('/summarize', [AIController::class, 'summarize']);
        Route::post('/rewrite', [AIController::class, 'rewrite']);
        Route::post('/questions', [AIController::class, 'questions']);
        Route::get('/history', [AIController::class, 'history']);
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
