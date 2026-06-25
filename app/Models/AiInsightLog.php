<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsightLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'prompt_hash',
        'response_text',
        'tokens_used',
        'model',
        'context_summary',
    ];

    protected function casts(): array
    {
        return [
            'context_summary' => 'array',
            'tokens_used'     => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, int $hours = 1)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
