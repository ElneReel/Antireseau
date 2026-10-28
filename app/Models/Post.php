<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
        'comments_allowed',
        'status',
        'content_hash',
        'edited_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'comments_allowed' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasOne
    {
        return $this->hasOne(Media::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'target_id')->where('target_type', 'post');
    }

    /**
     * Scope pour n'avoir que les publications actives.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope pour le fil commun chronologique strict.
     * Tri absolu : created_at DESC, id DESC.
     * Filtre les utilisateurs bloqués si un utilisateur est connecté.
     */
    public function scopeFeedFor(Builder $query, ?User $viewer = null): Builder
    {
        $query->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($viewer) {
            $conflictIds = $viewer->getBlockedUserIds();
            if (!empty($conflictIds)) {
                $query->whereNotIn('user_id', $conflictIds);
            }
        }

        return $query;
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return Bookmark::where('user_id', $user->id)
            ->where('post_id', $this->id)
            ->exists();
    }
}
