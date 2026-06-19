<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Supprimer un commentaire : réservé aux admins uniquement.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }
}
