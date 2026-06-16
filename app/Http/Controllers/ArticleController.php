<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="Mon API Blog",
 *     version="1.0.0",
 *     description="Documentation de mon API"
 * )
 * @OA\Server(
 *     url="http://127.0.0",
 *     description="Serveur Local"
 * )
 */
class ArticleController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/articles",
     *     summary="Liste paginée des articles avec recherche",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Mot-clé pour filtrer le titre ou le contenu",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Liste des articles récupérée")
     * )
     */
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $articles = $query->latest()->paginate(10);

        return response()->json($articles);
    }

    /**
     * @OA\Post(
     *     path="/articles",
     *     summary="Créer un article avec une image",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "short_description", "content"},
     *                 @OA\Property(property="title", type="string", example="Mon super titre"),
     *                 @OA\Property(property="short_description", type="string", example="Description courte"),
     *                 @OA\Property(property="content", type="string", example="Texte de l'article"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Article créé avec succès")
     * )
     */
    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
            $validated['image'] = $imagePath;
        }
        // Associe l'article à l'ID de l'utilisateur connecté
        $validated['user_id'] = $request->user()->id;
        // Crée l'article avec toutes les données (y compris l'user_id)
        $article = Article::create($validated);

        if ($article->image) {
            $article->image = asset('storage/' . $article->image);
        }

        return response()->json([
            'message' => 'Article créé avec succès avec son image !',
            'article' => $article
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/articles/{article}",
     *     summary="Afficher un article unique",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="ID de l'article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails de l'article"),
     *     @OA\Response(response=404, description="Article non trouvé")
     * )
     */
    public function show(Article $article)
    {
        return response()->json($article);
    }

    /**
     * @OA\Post(
     *     path="/articles/{article}",
     *     summary="Modifier un article (Simulé en POST pour l'upload d'image)",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="ID de l'article à modifier",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="title", type="string", example="Titre modifié"),
     *                 @OA\Property(property="short_description", type="string", example="Description modifiée"),
     *                 @OA\Property(property="content", type="string", example="Contenu modifié"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Article mis à jour avec succès")
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);
       
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }

            $imagePath = $request->file('image')->store('articles', 'public');
            $validated['image'] = $imagePath;
        }

        $article->update($validated);

        if ($article->image) {
            $article->image = asset('storage/' . $article->image);
        }

        return response()->json([
            'message' => 'Article et image mis à jour avec succès !',
            'article' => $article
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/articles/{article}",
     *     summary="Supprimer un article",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="ID de l'article à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Article supprimé avec succès")
     * )
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article et son image associés supprimés avec succès !'
        ]);
    }
}
