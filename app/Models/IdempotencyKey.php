<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdempotencyKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'idempotency_key',
        'status_code',
        'response_headers',
        'response_body',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'response_headers' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
