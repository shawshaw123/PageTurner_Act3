<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'role', 'content', 'provider',
        'model', 'tokens_used', 'cost_estimate', 'response_time', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost_estimate' => 'float',
        'response_time' => 'float',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
