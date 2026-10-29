<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'role',
        'status',
        'write_suspended_until',
        'write_suspended_reason',
        'terms_accepted_at',
        'is_adult_confirmed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'write_suspended_until' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'is_adult_confirmed' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(PostRepost::class);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')
            ->withTimestamps();
    }

    public function repostedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_reposts', 'user_id', 'post_id')
            ->withTimestamps();
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id')
            ->withTimestamps();
    }

    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'blocker_id')
            ->withTimestamps();
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * Vérifie si le compte est actuellement sous le coup d'une suspension d'écriture (24h).
     */
    public function isWritingSuspended(): bool
    {
        if ($this->write_suspended_until === null) {
            return false;
        }

        return Carbon::now()->lt($this->write_suspended_until);
    }

    /**
     * Renvoie les identifiants de tous les utilisateurs en conflit de blocage (bloqués par moi OU me bloquant).
     */
    public function getBlockedUserIds(): array
    {
        $blockedByMe = Block::where('blocker_id', $this->id)->pluck('blocked_id')->all();
        $blockingMe = Block::where('blocked_id', $this->id)->pluck('blocker_id')->all();

        return array_values(array_unique(array_merge($blockedByMe, $blockingMe)));
    }

    public function hasBlocked(int|User $user): bool
    {
        $targetId = $user instanceof User ? $user->id : $user;

        return Block::where('blocker_id', $this->id)
            ->where('blocked_id', $targetId)
            ->exists();
    }

    public function isBlockedBy(int|User $user): bool
    {
        $targetId = $user instanceof User ? $user->id : $user;

        return Block::where('blocker_id', $targetId)
            ->where('blocked_id', $this->id)
            ->exists();
    }
}
