<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Modifier un article :
     * - Admin peut tout modifier
     * - Author ne peut modifier que ses propres articles
     */
    public function update(User $user, Article $article): bool
    {
        return $user->isAdmin() || $user->id === $article->user_id;
    }

    /**
     * Supprimer un article :
     * - Admin peut tout supprimer
     * - Author ne peut supprimer que ses propres articles
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->isAdmin() || $user->id === $article->user_id;
    }
}
