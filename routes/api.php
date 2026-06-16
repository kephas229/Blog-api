<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;

// Route de diagnostic temporaire
Route::get('/debug', function () {
    try {
        DB::connection()->getPdo();
        $dbOk = true;
        $dbName = DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        $dbOk = false;
        $dbName = $e->getMessage();
    }
    return response()->json([
        'laravel'     => app()->version(),
        'env'         => app()->environment(),
        'db_driver'   => config('database.default'),
        'db_host'     => config('database.connections.' . config('database.default') . '.host'),
        'db_database' => config('database.connections.' . config('database.default') . '.database'),
        'db_connected'=> $dbOk,
        'db_message'  => $dbName,
    ]);
});

// 1. Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2. Routes de consultation publiques (Exemple : voir les articles)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

// 3. Routes privées sécurisées (Nécessitent d'être connecté avec un Token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Gestion des articles (Créer, modifier, supprimer)
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    
    // Gestion des commentaires
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

