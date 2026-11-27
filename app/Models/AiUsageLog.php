<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'provider', 'feature', 'model', 'prompt_tokens',
        'completion_tokens', 'total_tokens', 'cost_estimate',
        'response_time', 'success', 'error_message', 'user_id',
    ];

    protected $casts = [
        'success' => 'boolean',
        'cost_estimate' => 'float',
        'response_time' => 'float',
    ];
}
