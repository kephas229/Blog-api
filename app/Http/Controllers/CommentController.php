<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    #[OA\Post(
        path: '/api/comments',
        summary: 'Poster un commentaire',
        description: 'Ajoute un commentaire sur un article existant. Nécessite d\'être authentifié.',
        tags: ['Commentaires'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['visitor_name', 'visitor_email', 'message', 'article_id'],
                properties: [
                    new OA\Property(property: 'visitor_name',  type: 'string',  example: 'Jean Dupont',                          description: 'Nom du visiteur (max 191 caractères)'),
                    new OA\Property(property: 'visitor_email', type: 'string',  format: 'email', example: 'jean@exemple.fr',     description: 'Email du visiteur (max 191 caractères)'),
                    new OA\Property(property: 'message',       type: 'string',  example: 'Excellent article, très bien expliqué !', description: 'Contenu du commentaire (max 1000 caractères)'),
                    new OA\Property(property: 'article_id',    type: 'integer', example: 1,                                      description: 'ID de l\'article auquel ce commentaire est rattaché'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire publié',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Commentaire ajouté avec succès !'),
                    new OA\Property(property: 'comment', ref: '#/components/schemas/Comment'),
                ])
            ),
            new OA\Response(response: 401, description: 'Non authentifié',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
            new OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreCommentRequest $request)
    {
        $validated = $request->validated();
        $comment   = Comment::create($validated);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès !',
            'comment' => $comment
        ], 201);
    }

    #[OA\Delete(
        path: '/api/comments/{id}',
        summary: 'Supprimer un commentaire',
        description: 'Supprime un commentaire. Seul le propriétaire du commentaire ou un administrateur peut effectuer cette action.',
        tags: ['Commentaires'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du commentaire', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Commentaire supprimé',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Commentaire supprimé avec succès !')])),
            new OA\Response(response: 401, description: 'Non authentifié',           content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
            new OA\Response(response: 403, description: 'Non autorisé',              content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')])),
            new OA\Response(response: 404, description: 'Commentaire introuvable',   content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'No query results for model.')])),
        ]
    )]
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès !']);
    }
}
