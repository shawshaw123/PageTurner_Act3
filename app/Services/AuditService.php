<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Models\Audit;

class AuditService
{
    /**
     * Log a custom audit event.
     */
    public static function log(string $event, string $description, array $data = [])
    {
        $audit = new Audit([
            'event' => $event,
            'auditable_type' => $data['auditable_type'] ?? null,
            'auditable_id' => $data['auditable_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'user_id' => Auth::id(),
        ]);

        // Add custom metadata
        if (!empty($data['metadata'])) {
            $audit->metadata = $data['metadata'];
        }

        $audit->save();

        return $audit;
    }

    /**
     * Log authentication events.
     */
    public static function logAuth(string $event, array $data = [])
    {
        return self::log($event, 'Authentication event', array_merge($data, [
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => Auth::id(),
        ]));
    }

    /**
     * Log security events.
     */
    public static function logSecurity(string $event, string $description, array $data = [])
    {
        return self::log($event, $description, array_merge($data, [
            'metadata' => array_merge($data['metadata'] ?? [], [
                'category' => 'security',
                'severity' => $data['severity'] ?? 'medium',
            ]),
        ]));
    }

    /**
     * Log system events.
     */
    public static function logSystem(string $event, string $description, array $data = [])
    {
        return self::log($event, $description, array_merge($data, [
            'metadata' => array_merge($data['metadata'] ?? [], [
                'category' => 'system',
                'initiated_by' => Auth::user() ? Auth::user()->name : 'system',
            ]),
        ]));
    }

    /**
     * Log data import/export events.
     */
    public static function logDataOperation(string $event, string $operation, array $data = [])
    {
        return self::log($event, "Data {$operation}", array_merge($data, [
            'metadata' => array_merge($data['metadata'] ?? [], [
                'category' => 'data_operation',
                'operation' => $operation,
            ]),
        ]));
    }

    /**
     * Get audit statistics.
     */
    public static function getStatistics($days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'total_audits' => Audit::where('created_at', '>=', $startDate)->count(),
            'unique_users' => Audit::where('created_at', '>=', $startDate)
                                  ->distinct('user_id')
                                  ->count('user_id'),
            'events_by_day' => Audit::where('created_at', '>=', $startDate)
                                   ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                   ->groupBy('date')
                                   ->orderBy('date')
                                   ->get(),
            'top_events' => Audit::where('created_at', '>=', $startDate)
                               ->selectRaw('event, COUNT(*) as count')
                               ->groupBy('event')
                               ->orderBy('count', 'desc')
                               ->limit(10)
                               ->get(),
            'top_models' => Audit::where('created_at', '>=', $startDate)
                               ->selectRaw('auditable_type, COUNT(*) as count')
                               ->groupBy('auditable_type')
                               ->orderBy('count', 'desc')
                               ->limit(10)
                               ->get(),
        ];
    }

    /**
     * Generate audit summary report.
     */
    public static function generateSummaryReport($startDate, $endDate)
    {
        $query = Audit::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $startDate->diffInDays($endDate),
            ],
            'summary' => [
                'total_audits' => $query->count(),
                'unique_users' => $query->distinct('user_id')->count('user_id'),
                'unique_models' => $query->distinct('auditable_type')->count('auditable_type'),
            ],
            'events' => $query->selectRaw('event, COUNT(*) as count')
                            ->groupBy('event')
                            ->orderBy('count', 'desc')
                            ->get(),
            'models' => $query->selectRaw('auditable_type, COUNT(*) as count')
                            ->groupBy('auditable_type')
                            ->orderBy('count', 'desc')
                            ->get(),
            'users' => $query->with('user')
                            ->selectRaw('user_id, COUNT(*) as count')
                            ->groupBy('user_id')
                            ->orderBy('count', 'desc')
                            ->limit(10)
                            ->get(),
            'security_events' => $query->where('metadata->category', 'security')
                                     ->count(),
            'system_events' => $query->where('metadata->category', 'system')
                                   ->count(),
        ];
    }

    /**
     * Check for suspicious activity patterns.
     */
    public static function detectSuspiciousActivity($hours = 24)
    {
        $since = now()->subHours($hours);
        $alerts = [];

        // Check for multiple failed logins
        $failedLogins = Audit::where('event', 'failed_login')
                           ->where('created_at', '>=', $since)
                           ->groupBy('ip_address')
                           ->havingRaw('COUNT(*) >= 10')
                           ->get();

        if ($failedLogins->isNotEmpty()) {
            $alerts[] = [
                'type' => 'multiple_failed_logins',
                'severity' => 'high',
                'message' => 'Multiple failed login attempts detected from ' . $failedLogins->count() . ' IP addresses',
                'data' => $failedLogins,
            ];
        }

        // Check for rapid model changes
        $rapidChanges = Audit::where('event', 'updated')
                           ->where('created_at', '>=', $since)
                           ->groupBy('user_id', 'auditable_type')
                           ->havingRaw('COUNT(*) >= 50')
                           ->get();

        if ($rapidChanges->isNotEmpty()) {
            $alerts[] = [
                'type' => 'rapid_changes',
                'severity' => 'medium',
                'message' => 'Rapid model changes detected',
                'data' => $rapidChanges,
            ];
        }

        // Check for admin activity from unusual IPs
        $adminActivity = Audit::whereHas('user', function ($q) {
                                $q->where('role', 'admin');
                            })
                            ->where('created_at', '>=', $since)
                            ->whereNotIn('ip_address', function ($q) {
                                $q->select('ip_address')
                                  ->from('audits')
                                  ->whereHas('user', function ($q) {
                                      $q->where('role', 'admin');
                                  })
                                  ->where('created_at', '<', now()->subDays(7))
                                  ->distinct();
                            })
                            ->get();

        if ($adminActivity->isNotEmpty()) {
            $alerts[] = [
                'type' => 'unusual_admin_activity',
                'severity' => 'high',
                'message' => 'Admin activity detected from unusual IP addresses',
                'data' => $adminActivity,
            ];
        }

        return $alerts;
    }
}
