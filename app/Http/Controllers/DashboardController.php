<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article; // <-- Utilisez App\Models\Post si votre modèle s'appelle Post
use App\Models\Comment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Récupérer les statistiques globales du tableau de bord
     */
    public function index()
    {
        // 1. On compte le nombre total de données dans chaque table
        $totalArticles = Article::count();
        $totalUsers = User::count();
        $totalComments = Comment::count();

        // 2. Optionnel : On récupère les 5 derniers articles créés pour l'affichage rapide
        $latestArticles = Article::latest()->take(5)->get();

        // 3. On retourne toutes ces données au format JSON
        return response()->json([
            'stats' => [
                'total_articles' => $totalArticles,
                'total_users' => $totalUsers,
                'total_comments' => $totalComments,
            ],
            'latest_articles' => $latestArticles
        ]);
    }
}
