<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'BlogFlow API',
    version: '1.0.0',
    description: 'API REST de la plateforme BlogFlow. Authentification via token Bearer (Laravel Sanctum). Obtenez votre token via POST /api/login.',
    contact: new OA\Contact(name: 'Équipe BlogFlow', email: 'admin@blogflow.fr')
)]
#[OA\Server(url: 'https://blog-api-service-fbnq.onrender.com', description: 'Serveur de production')]
#[OA\Server(url: 'http://localhost:8000', description: 'Serveur local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token',
    description: 'Token obtenu via POST /api/login. Format : Bearer {token}'
)]
#[OA\Tag(name: 'Authentification', description: 'Inscription, connexion et déconnexion')]
#[OA\Tag(name: 'Articles',         description: 'Gestion des articles du blog')]
#[OA\Tag(name: 'Commentaires',     description: 'Gestion des commentaires sur les articles')]
#[OA\Tag(name: 'Tableau de bord',  description: 'Statistiques et données administratives')]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id',         type: 'integer', example: 1),
        new OA\Property(property: 'name',        type: 'string',  example: 'Sophie Marchand'),
        new OA\Property(property: 'email',       type: 'string',  format: 'email', example: 'admin@blogflow.fr'),
        new OA\Property(property: 'created_at',  type: 'string',  format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Category',
    properties: [
        new OA\Property(property: 'id',   type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string',  example: 'Développement Web'),
    ]
)]
#[OA\Schema(
    schema: 'Comment',
    properties: [
        new OA\Property(property: 'id',            type: 'integer', example: 1),
        new OA\Property(property: 'visitor_name',  type: 'string',  example: 'Jean Dupont'),
        new OA\Property(property: 'visitor_email', type: 'string',  format: 'email', example: 'jean@exemple.fr'),
        new OA\Property(property: 'message',       type: 'string',  example: 'Excellent article !'),
        new OA\Property(property: 'article_id',    type: 'integer', example: 1),
        new OA\Property(property: 'created_at',    type: 'string',  format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Article',
    properties: [
        new OA\Property(property: 'id',                type: 'integer', example: 1),
        new OA\Property(property: 'title',             type: 'string',  example: 'Les fondamentaux de React 19'),
        new OA\Property(property: 'short_description', type: 'string',  example: 'React 19 introduit des changements profonds...'),
        new OA\Property(property: 'content',           type: 'string',  example: 'Contenu complet de l\'article...'),
        new OA\Property(property: 'image',             type: 'string',  nullable: true, example: 'https://blog-api-service-fbnq.onrender.com/storage/articles/photo.jpg'),
        new OA\Property(property: 'user_id',           type: 'integer', example: 1),
        new OA\Property(property: 'category_id',       type: 'integer', example: 1),
        new OA\Property(property: 'created_at',        type: 'string',  format: 'date-time'),
        new OA\Property(property: 'updated_at',        type: 'string',  format: 'date-time'),
        new OA\Property(property: 'category',          ref: '#/components/schemas/Category'),
        new OA\Property(property: 'user',              ref: '#/components/schemas/User'),
    ]
)]
#[OA\Schema(
    schema: 'ArticleWithComments',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Article'),
        new OA\Schema(properties: [
            new OA\Property(property: 'comments', type: 'array', items: new OA\Items(ref: '#/components/schemas/Comment')),
        ]),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedArticles',
    properties: [
        new OA\Property(property: 'current_page',  type: 'integer', example: 1),
        new OA\Property(property: 'last_page',     type: 'integer', example: 2),
        new OA\Property(property: 'per_page',      type: 'integer', example: 10),
        new OA\Property(property: 'total',         type: 'integer', example: 12),
        new OA\Property(property: 'next_page_url', type: 'string',  nullable: true),
        new OA\Property(property: 'prev_page_url', type: 'string',  nullable: true),
        new OA\Property(property: 'data',          type: 'array',   items: new OA\Items(ref: '#/components/schemas/Article')),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
        new OA\Property(property: 'errors',  type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))),
    ]
)]
class ArticleController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/api/articles',
        summary: 'Liste paginée des articles',
        description: 'Retourne 10 articles par page avec leurs catégories et auteurs. Supporte la recherche plein texte et le filtrage par catégorie.',
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(name: 'page',        in: 'query', required: false, description: 'Numéro de page (défaut : 1)',                    schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'search',      in: 'query', required: false, description: 'Recherche dans le titre et le contenu',          schema: new OA\Schema(type: 'string',  example: 'react')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, description: 'Filtrer par identifiant de catégorie',           schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste récupérée avec succès', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedArticles')),
        ]
    )]
    public function index(Request $request)
    {
        $query = Article::with(['category', 'user']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $articles = $query->latest()->paginate(10);

        $articles->getCollection()->transform(function ($article) {
            if ($article->image) {
                $article->image = asset('storage/' . $article->image);
            }
            return $article;
        });

        return response()->json($articles);
    }

    #[OA\Post(
        path: '/api/articles',
        summary: 'Créer un article',
        description: 'Crée un nouvel article. L\'auteur est automatiquement l\'utilisateur authentifié.',
        tags: ['Articles'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'short_description', 'content', 'category_id'],
                    properties: [
                        new OA\Property(property: 'title',             type: 'string',  example: 'Mon nouvel article',          description: 'Max 191 caractères'),
                        new OA\Property(property: 'short_description', type: 'string',  example: 'Description courte',          description: 'Max 255 caractères'),
                        new OA\Property(property: 'content',           type: 'string',  example: 'Contenu complet de l\'article'),
                        new OA\Property(property: 'category_id',       type: 'integer', example: 1,                             description: 'ID d\'une catégorie existante'),
                        new OA\Property(property: 'image',             type: 'string',  format: 'binary',                       description: 'jpeg/png/jpg/gif, max 2 Mo'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Article créé',         content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Article créé avec succès !'), new OA\Property(property: 'article', ref: '#/components/schemas/Article')])),
            new OA\Response(response: 401, description: 'Non authentifié',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
            new OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['user_id'] = $request->user()->id;
        $article = Article::create($validated);

        if ($article->image) {
            $article->image = asset('storage/' . $article->image);
        }

        return response()->json([
            'message' => 'Article créé avec succès avec son image !',
            'article' => $article
        ], 201);
    }

    #[OA\Get(
        path: '/api/articles/{id}',
        summary: 'Détail d\'un article',
        description: 'Retourne un article complet avec sa catégorie, son auteur et tous ses commentaires.',
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l\'article', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Article trouvé',      content: new OA\JsonContent(ref: '#/components/schemas/ArticleWithComments')),
            new OA\Response(response: 404, description: 'Article introuvable', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'No query results for model.')])),
        ]
    )]
    public function show(Article $article)
    {
        $article->load(['category', 'user', 'comments']);

        if ($article->image) {
            $article->image = asset('storage/' . $article->image);
        }

        return response()->json($article);
    }

    #[OA\Put(
        path: '/api/articles/{id}',
        summary: 'Modifier un article',
        description: 'Met à jour un article. Seul l\'auteur peut modifier son article. Pour upload d\'image depuis un formulaire HTML, utiliser POST avec _method=PUT.',
        tags: ['Articles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l\'article', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'title',             type: 'string',  example: 'Titre modifié'),
                    new OA\Property(property: 'short_description', type: 'string',  example: 'Nouvelle description'),
                    new OA\Property(property: 'content',           type: 'string',  example: 'Nouveau contenu'),
                    new OA\Property(property: 'image',             type: 'string',  format: 'binary'),
                    new OA\Property(property: '_method',           type: 'string',  example: 'PUT', description: 'Spoof méthode HTTP'),
                ])
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Article mis à jour',   content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Article mis à jour !'), new OA\Property(property: 'article', ref: '#/components/schemas/Article')])),
            new OA\Response(response: 401, description: 'Non authentifié',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
            new OA\Response(response: 403, description: 'Non autorisé',         content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')])),
            new OA\Response(response: 404, description: 'Article introuvable',  content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'No query results for model.')])),
            new OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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

    #[OA\Delete(
        path: '/api/articles/{id}',
        summary: 'Supprimer un article',
        description: 'Supprime un article et son image. Seul l\'auteur peut supprimer son article.',
        tags: ['Articles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l\'article', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Article supprimé',     content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Article supprimé avec succès !')])),
            new OA\Response(response: 401, description: 'Non authentifié',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
            new OA\Response(response: 403, description: 'Non autorisé',         content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')])),
            new OA\Response(response: 404, description: 'Article introuvable',  content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'No query results for model.')])),
        ]
    )]
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
