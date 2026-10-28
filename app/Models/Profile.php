<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'handle',
        'handle_lower',
        'display_name',
        'bio',
        'avatar_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accesseur pour le nom affiché (retombe sur le handle si non renseigné).
     */
    public function getEffectiveNameAttribute(): string
    {
        return !empty($this->display_name) ? $this->display_name : '@' . $this->handle;
    }
}
