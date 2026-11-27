<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Every webhook call received (IP-3), kept for auditing and to process each event only once. */
class WebhookEvent extends Model
{
    protected $fillable = [
        'provider', 'event_id', 'type', 'signature_valid', 'status', 'payload', 'error', 'received_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
