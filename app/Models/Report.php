<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'target_type',
        'target_id',
        'reason',
        'details',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function evidence(): HasOne
    {
        return $this->hasOne(ReportEvidence::class);
    }

    /**
     * Récupère la cible actuelle (si non encore supprimée).
     */
    public function getTargetInstance()
    {
        return match ($this->target_type) {
            'post' => Post::find($this->target_id),
            'comment' => Comment::find($this->target_id),
            'user' => User::find($this->target_id),
            default => null,
        };
    }
}
