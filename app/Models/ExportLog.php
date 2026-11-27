<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'model_type',
        'format',
        'filters',
        'columns',
        'total_records',
        'status',
        'file_path',
        'download_url',
        'expires_at',
        'started_at',
        'completed_at',
        'updated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        return $end->diffInSeconds($this->started_at);
    }

    public function getDownloadableUrlAttribute(): ?string
    {
        if (!$this->isCompleted() || $this->isExpired()) {
            return null;
        }

        return route('exports.download', $this->id);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
