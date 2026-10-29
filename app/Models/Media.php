<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'user_id',
        'post_id',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'media_type',
        'file_size',
        'width',
        'height',
        'duration_seconds',
        'alt_text',
        'file_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video' || str_starts_with($this->mime_type, 'video/');
    }

    public function isAudio(): bool
    {
        return $this->media_type === 'audio' || str_starts_with($this->mime_type, 'audio/');
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image' || str_starts_with($this->mime_type, 'image/');
    }
}
