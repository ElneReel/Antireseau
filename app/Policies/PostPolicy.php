<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * N'importe quel membre connecté peut voir un post publié, sauf si conflit de blocage.
     */
    public function view(User $user, Post $post): bool
    {
        if ($post->status !== 'published') {
            return $user->isAdmin() || $user->id === $post->user_id;
        }

        // Si l'auteur est bloqué ou bloque le visiteur
        if ($user->hasBlocked($post->user_id) || $user->isBlockedBy($post->user_id)) {
            return false;
        }

        return true;
    }

    /**
     * Un utilisateur peut modifier uniquement ses propres publications.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && $post->status === 'published';
    }

    /**
     * Un utilisateur peut supprimer ses propres publications, ou un administrateur.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    /**
     * Un utilisateur peut commenter si le post autorise les commentaires, n'est pas masqué/supprimé,
     * et s'il n'y a pas de conflit de blocage.
     */
    public function comment(User $user, Post $post): bool
    {
        if (!$post->comments_allowed || $post->status !== 'published') {
            return false;
        }

        if ($user->hasBlocked($post->user_id) || $user->isBlockedBy($post->user_id)) {
            return false;
        }

        if ($user->isWritingSuspended()) {
            return false;
        }

        return true;
    }

    /**
     * L'auteur peut basculer l'état des commentaires (ouvert/fermé).
     */
    public function toggleComments(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
