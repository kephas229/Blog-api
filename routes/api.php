<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

// 1. Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// 2. Routes de consultation publiques
Route::get('/articles',          [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// 3. Poster un commentaire — accessible sans authentification
Route::post('/comments', [CommentController::class, 'store']);

// 4. Routes accessibles aux utilisateurs authentifiés (author + admin)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Profil de l'utilisateur connecté
    Route::get('/me',    [UserController::class, 'me']);
    Route::put('/me',    [UserController::class, 'updateProfile']);

    // Gestion des articles (author crée/modifie/supprime les siens ; admin tout)
    Route::post('/articles',              [ArticleController::class, 'store']);
    Route::put('/articles/{article}',     [ArticleController::class, 'update']);
    Route::delete('/articles/{article}',  [ArticleController::class, 'destroy']);

    // Suppression de commentaire (admin seulement, vérifié par CommentPolicy)
    Route::delete('/comments/{comment}',  [CommentController::class, 'destroy']);

    // Tableau de bord (admin seulement, vérifié dans le controller)
    Route::get('/dashboard',              [DashboardController::class, 'index']);

    // Gestion des utilisateurs (admin seulement)
    Route::get('/users',                  [UserController::class, 'index']);
    Route::patch('/users/{user}/role',    [UserController::class, 'updateRole']);
    Route::delete('/users/{user}',        [UserController::class, 'destroy']);
});
