<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    /**
     * 1. CRÉATION (Ajouter un commentaire sur un article)
     */
    public function store(StoreCommentRequest $request)
    {
        // Les données sont validées grâce à StoreCommentRequest
        $validated = $request->validated();

        // Si l'utilisateur est connecté, on peut lier son ID (facultatif mais recommandé)
        // $validated['user_id'] = auth()->id();

        // On crée le commentaire en base de données
        $comment = Comment::create($validated);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès !',
            'comment' => $comment
        ], 201);
    }

    /**
     * 2. SUPPRESSION (Effacer un commentaire via son ID)
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment); // Vérifie que l'utilisateur connecté a le droit de supprimer ce commentaire
        // On supprime le commentaire
        $comment->delete();

        return response()->json([
            'message' => 'Commentaire supprimé avec succès !'
        ]);
    }
}
