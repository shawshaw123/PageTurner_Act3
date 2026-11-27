<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One email (IP-4) and how long it took from being queued to being sent. */
class NotificationLog extends Model
{
    protected $fillable = [
        'notification', 'channel', 'recipient', 'order_id', 'status', 'queued_at', 'sent_at', 'delivery_seconds', 'error',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
