<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Article $article): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Déterminer si l'utilisateur peut modifier l'article.
     */
    public function update(User $user, Article $article): bool
    {
        // On autorise si l'ID de l'utilisateur correspond au user_id de l'article
        return $user->id === $article->user_id;
    }

    /**
     * Déterminer si l'utilisateur peut supprimer l'article.
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    /**
     * Determine si l'utilisateur peut restaurer le modèle.
     */
    public function restore(User $user, Article $article): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Article $article): bool
    {
        return false;
    }
}
