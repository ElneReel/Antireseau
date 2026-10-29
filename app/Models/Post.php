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
        'bumped_at',
        'last_reposted_by_id',
        'created_at',
        'updated_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (!$post->bumped_at) {
                $post->bumped_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'comments_allowed' => 'boolean',
            'edited_at' => 'datetime',
            'bumped_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastRepostedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reposted_by_id');
    }

    public function media(): HasOne
    {
        return $this->hasOne(Media::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(PostRepost::class);
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
     * Scope pour le fil commun.
     * En mode 'bumped' (par défaut), un repartage fait remonter le post en haut du fil (bumped_at DESC).
     * En mode 'chrono', tri strict sur la date de création initiale (created_at DESC).
     */
    public function scopeFeedFor(Builder $query, ?User $viewer = null, string $mode = 'bumped'): Builder
    {
        $query->where('status', 'published');

        if ($mode === 'chrono') {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderByRaw('COALESCE(bumped_at, created_at) DESC')->orderBy('id', 'desc');
        }

        if ($viewer) {
            $conflictIds = $viewer->getBlockedUserIds();
            if (!empty($conflictIds)) {
                $query->whereNotIn('user_id', $conflictIds);
            }
        }

        return $query;
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return PostLike::where('user_id', $user->id)
            ->where('post_id', $this->id)
            ->exists();
    }

    public function isRepostedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return PostRepost::where('user_id', $user->id)
            ->where('post_id', $this->id)
            ->exists();
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
