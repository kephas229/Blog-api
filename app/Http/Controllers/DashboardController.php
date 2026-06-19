<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard',
        summary: 'Statistiques du tableau de bord',
        description: 'Retourne les compteurs globaux (articles, utilisateurs, commentaires) et les 5 derniers articles publiés avec leurs relations.',
        tags: ['Tableau de bord'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques récupérées',
                content: new OA\JsonContent(properties: [
                    new OA\Property(
                        property: 'stats',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'total_articles', type: 'integer', example: 12),
                            new OA\Property(property: 'total_users',    type: 'integer', example: 5),
                            new OA\Property(property: 'total_comments', type: 'integer', example: 23),
                        ]
                    ),
                    new OA\Property(
                        property: 'latest_articles',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/Article')
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Non authentifié', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
        ]
    )]
    public function index()
    {
        // Vérification du rôle admin
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        $totalArticles  = Article::count();
        $totalUsers     = User::count();
        $totalComments  = Comment::count();
        $latestArticles = Article::with(['category', 'user'])->latest()->take(5)->get();

        return response()->json([
            'stats' => [
                'total_articles' => $totalArticles,
                'total_users'    => $totalUsers,
                'total_comments' => $totalComments,
            ],
            'latest_articles' => $latestArticles
        ]);
    }
}
