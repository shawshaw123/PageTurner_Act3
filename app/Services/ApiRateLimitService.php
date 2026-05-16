<?php

namespace App\Services;

use App\Models\ApiRateLimit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class ApiRateLimitService
{
    /**
     * Get current rate limit status for a user/IP.
     */
    public static function getCurrentStatus($identifier, string $identifierType = 'user'): array
    {
        $key = $identifierType === 'user' ? "api:user:{$identifier}" : "api:ip:{$identifier}";
        $attempts = RateLimiter::attempts($key);
        $limit = self::getLimitForIdentifier($identifier, $identifierType);
        
        return [
            'identifier' => $identifier,
            'identifier_type' => $identifierType,
            'attempts' => $attempts,
            'limit' => $limit,
            'remaining' => max(0, $limit - $attempts),
            'reset_at' => RateLimiter::availableIn($key),
        ];
    }

    /**
     * Get rate limit statistics.
     */
    public static function getStatistics($hours = 24): array
    {
        $since = now()->subHours($hours);
        
        return [
            'total_requests' => ApiRateLimit::where('window_start', '>=', $since)->sum('requests_count'),
            'unique_users' => ApiRateLimit::where('identifier_type', 'user')
                                       ->where('window_start', '>=', $since)
                                       ->distinct('identifier')
                                       ->count('identifier'),
            'unique_ips' => ApiRateLimit::where('identifier_type', 'ip')
                                    ->where('window_start', '>=', $since)
                                    ->distinct('identifier')
                                    ->count('identifier'),
            'tier_distribution' => ApiRateLimit::where('window_start', '>=', $since)
                                            ->selectRaw('tier, COUNT(*) as count, SUM(requests_count) as total_requests')
                                            ->groupBy('tier')
                                            ->get(),
            'top_endpoints' => ApiRateLimit::where('window_start', '>=', $since)
                                         ->selectRaw('endpoint, COUNT(*) as count, SUM(requests_count) as total_requests')
                                         ->groupBy('endpoint')
                                         ->orderBy('total_requests', 'desc')
                                         ->limit(10)
                                         ->get(),
            'rate_limit_hits' => ApiRateLimit::where('window_start', '>=', $since)
                                          ->whereRaw('requests_count >= limit')
                                          ->count(),
        ];
    }

    /**
     * Get rate limit for specific identifier.
     */
    protected static function getLimitForIdentifier($identifier, string $identifierType): int
    {
        if ($identifierType === 'user') {
            $user = \App\Models\User::find($identifier);
            if (!$user) return 30;
            
            return match($user->role) {
                'admin' => 1000,
                'premium', 'vip' => 300,
                default => 60,
            };
        }
        
        return 30; // Public tier for IPs
    }

    /**
     * Clear rate limits for a user/IP.
     */
    public static function clearRateLimit($identifier, string $identifierType = 'user'): void
    {
        $key = $identifierType === 'user' ? "api:user:{$identifier}" : "api:ip:{$identifier}";
        RateLimiter::clear($key);
        
        // Also clear burst protection key
        RateLimiter::clear($key . ':burst');
    }

    /**
     * Set custom rate limit for a user.
     */
    public static function setCustomLimit($userId, int $limit, int $durationMinutes = 60): void
    {
        $key = "api:custom:user:{$userId}";
        Cache::put($key, $limit, $durationMinutes * 60);
    }

    /**
     * Get custom limit for a user.
     */
    public static function getCustomLimit($userId): ?int
    {
        $key = "api:custom:user:{$userId}";
        return Cache::get($key);
    }

    /**
     * Block a user/IP from API access.
     */
    public static function blockIdentifier($identifier, string $identifierType = 'user', int $durationMinutes = 60): void
    {
        $key = $identifierType === 'user' ? "api:block:user:{$identifier}" : "api:block:ip:{$identifier}";
        Cache::put($key, true, $durationMinutes * 60);
    }

    /**
     * Check if a user/IP is blocked.
     */
    public static function isBlocked($identifier, string $identifierType = 'user'): bool
    {
        $key = $identifierType === 'user' ? "api:block:user:{$identifier}" : "api:block:ip:{$identifier}";
        return Cache::has($key);
    }

    /**
     * Get rate limit analytics for dashboard.
     */
    public static function getDashboardData(): array
    {
        $now = now();
        $lastHour = $now->copy()->subHour();
        $lastDay = $now->copy()->subDay();
        $lastWeek = $now->copy()->subWeek();

        return [
            'current_hour' => [
                'requests' => ApiRateLimit::where('window_start', '>=', $lastHour)->sum('requests_count'),
                'rate_limit_hits' => ApiRateLimit::where('window_start', '>=', $lastHour)
                                            ->whereRaw('requests_count >= limit')
                                            ->count(),
            ],
            'last_24_hours' => [
                'requests' => ApiRateLimit::where('window_start', '>=', $lastDay)->sum('requests_count'),
                'rate_limit_hits' => ApiRateLimit::where('window_start', '>=', $lastDay)
                                            ->whereRaw('requests_count >= limit')
                                            ->count(),
                'unique_users' => ApiRateLimit::where('window_start', '>=', $lastDay)
                                            ->where('identifier_type', 'user')
                                            ->distinct('identifier')
                                            ->count('identifier'),
            ],
            'last_7_days' => [
                'requests' => ApiRateLimit::where('window_start', '>=', $lastWeek)->sum('requests_count'),
                'daily_breakdown' => ApiRateLimit::where('window_start', '>=', $lastWeek)
                                               ->selectRaw('DATE(window_start) as date, SUM(requests_count) as requests')
                                               ->groupBy('date')
                                               ->orderBy('date')
                                               ->get(),
            ],
            'top_consumers' => ApiRateLimit::where('window_start', '>=', $lastDay)
                                         ->selectRaw('identifier, identifier_type, SUM(requests_count) as total_requests')
                                         ->groupBy('identifier', 'identifier_type')
                                         ->orderBy('total_requests', 'desc')
                                         ->limit(10)
                                         ->get(),
        ];
    }

    /**
     * Detect suspicious API usage patterns.
     */
    public static function detectSuspiciousActivity(): array
    {
        $alerts = [];
        $lastHour = now()->subHour();

        // Check for users hitting rate limits consistently
        $abusiveUsers = ApiRateLimit::where('window_start', '>=', $lastHour)
                                   ->whereRaw('requests_count >= limit * 0.9') // 90% of limit
                                   ->where('identifier_type', 'user')
                                   ->groupBy('identifier')
                                   ->havingRaw('COUNT(*) >= 3') // At least 3 times in the last hour
                                   ->get(['identifier']);

        if ($abusiveUsers->isNotEmpty()) {
            $alerts[] = [
                'type' => 'high_usage_users',
                'severity' => 'medium',
                'message' => 'Users consistently hitting rate limits',
                'data' => $abusiveUsers,
            ];
        }

        // Check for unusual spike in requests
        $avgRequests = ApiRateLimit::where('window_start', '>=', now()->subDays(7))
                                 ->avg('requests_count');
        
        $currentHour = ApiRateLimit::where('window_start', '>=', $lastHour)
                                 ->avg('requests_count');

        if ($currentHour > $avgRequests * 3) { // 3x spike
            $alerts[] = [
                'type' => 'request_spike',
                'severity' => 'high',
                'message' => 'Unusual spike in API requests detected',
                'data' => [
                    'current_hour' => $currentHour,
                    'average' => $avgRequests,
                    'multiplier' => round($currentHour / $avgRequests, 2),
                ],
            ];
        }

        // Check for many requests from single IP
        $highVolumeIps = ApiRateLimit::where('window_start', '>=', $lastHour)
                                    ->where('identifier_type', 'ip')
                                    ->where('requests_count', '>', 100)
                                    ->groupBy('identifier')
                                    ->havingRaw('COUNT(*) >= 5')
                                    ->get(['identifier']);

        if ($highVolumeIps->isNotEmpty()) {
            $alerts[] = [
                'type' => 'high_volume_ips',
                'severity' => 'high',
                'message' => 'High volume requests from specific IPs',
                'data' => $highVolumeIps,
            ];
        }

        return $alerts;
    }
}
