<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;

// 1. Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2. Routes de consultation publiques
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// 3. Poster un commentaire — accessible sans authentification
Route::post('/comments', [CommentController::class, 'store']);

// 4. Routes privées sécurisées (token Bearer requis)
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Gestion des articles
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    
    // Suppression de commentaire (modération admin)
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

