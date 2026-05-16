<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Audit as AuditContract;
use OwenIt\Auditing\Models\Audit as AuditModel;

class Audit extends AuditModel implements AuditContract
{
    protected $table = 'audits';

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'metadata' => 'json',
    ];

    public function getDiffAttribute(): array
    {
        $diff = [];
        
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];
        
        // Get all keys from both old and new values
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        
        foreach ($allKeys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $diff[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        
        return $diff;
    }

    public function getFormattedOldValuesAttribute(): array
    {
        return $this->formatValues($this->old_values);
    }

    public function getFormattedNewValuesAttribute(): array
    {
        return $this->formatValues($this->new_values);
    }

    protected function formatValues(array $values): array
    {
        $formatted = [];
        
        foreach ($values as $key => $value) {
            $formatted[$key] = $this->formatValue($value);
        }
        
        return $formatted;
    }

    protected function formatValue($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        
        return $value;
    }

    public function scopeForModel($query, $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function getHumanReadableEventAttribute(): string
    {
        return match($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            default => ucfirst($this->event),
        };
    }

    public function getIsCriticalAttribute(): bool
    {
        $criticalEvents = ['deleted', 'permission_changed', 'role_changed'];
        $criticalModels = [User::class];
        
        return in_array($this->event, $criticalEvents) || 
               in_array($this->auditable_type, $criticalModels);
    }
}
