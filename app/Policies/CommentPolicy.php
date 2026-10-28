<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * L'auteur du commentaire, l'auteur de la publication ou un admin peuvent supprimer un commentaire.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $user->id === $comment->post->user_id
            || $user->isAdmin();
    }
}
