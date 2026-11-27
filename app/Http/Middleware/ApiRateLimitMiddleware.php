<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use App\Models\ApiRateLimit;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRequestSignature($request);
        $tier = $this->getUserTier($request);
        $limit = $this->getTierLimit($tier, $request->route()->getName());
        
        // Check per-second burst protection
        if ($this->isBursting($key, $tier)) {
            return $this->rateLimitResponse($request, $limit, 0, 'Too many requests in quick succession');
        }

        // Check per-minute limit
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->rateLimitResponse($request, $limit, $seconds);
        }

        RateLimiter::hit($key);

        // Log rate limit hit for monitoring
        $this->logRateLimitHit($request, $key, $tier, $limit);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit - RateLimiter::attempts($key)));
        $response->headers->set('X-RateLimit-Tier', $tier);

        return $response;
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'api:user:' . $user->id . ':' . $request->ip();
        }

        return 'api:ip:' . $request->ip();
    }

    /**
     * Get user tier based on authentication and role.
     */
    protected function getUserTier(Request $request): string
    {
        $user = $request->user();
        
        if (!$user) {
            return 'public';
        }

        // Check if this is an auth-related endpoint
        $authRoutes = ['login', 'register', 'password.reset', 'password.email'];
        if (in_array($request->route()->getName(), $authRoutes)) {
            return 'auth';
        }

        // Check user role/subscription
        return match($user->role) {
            'admin' => 'admin',
            'premium', 'vip' => 'premium',
            default => 'standard',
        };
    }

    /**
     * Get rate limit based on tier and endpoint.
     */
    protected function getTierLimit(string $tier, ?string $endpoint = null): int
    {
        $limits = [
            'public' => 30,      // 30 requests per minute
            'auth' => 10,        // 10 requests per minute (strict for auth)
            'standard' => 60,    // 60 requests per minute
            'premium' => 300,    // 300 requests per minute
            'admin' => 1000,     // 1000 requests per minute
        ];

        // Adjust limits for specific sensitive endpoints
        if ($endpoint && str_contains($endpoint, 'admin')) {
            return (int) ($limits[$tier] * 0.8); // 20% reduction for admin endpoints
        }

        return $limits[$tier] ?? 30;
    }

    /**
     * Check for burst activity (per-second protection).
     */
    protected function isBursting(string $key, string $tier): bool
    {
        $burstLimits = [
            'public' => 2,       // 2 requests per second
            'auth' => 1,         // 1 request per second
            'standard' => 3,     // 3 requests per second
            'premium' => 5,      // 5 requests per second
            'admin' => 10,       // 10 requests per second
        ];

        $burstKey = $key . ':burst';
        $maxBurst = $burstLimits[$tier] ?? 2;

        // Use a simple sliding window approach
        $current = now()->timestamp;
        $window = 1; // 1 second window
        
        // Clean old entries
        RateLimiter::clear($burstKey);
        
        // Check current window
        $attempts = RateLimiter::attempts($burstKey);
        
        if ($attempts >= $maxBurst) {
            return true;
        }

        RateLimiter::hit($burstKey, $window);
        
        return false;
    }

    /**
     * Create rate limit response.
     */
    protected function rateLimitResponse(Request $request, int $limit, int $seconds = null, string $message = null): \Illuminate\Http\JsonResponse
    {
        $message = $message ?? 'Too many requests. Please try again later.';
        
        $response = [
            'error' => 'RATE_LIMIT_EXCEEDED',
            'message' => $message,
            'limit' => $limit,
            'retry_after' => $seconds,
        ];

        return Response::json($response, 429)->withHeaders([
            'Retry-After' => $seconds,
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Retry-After' => $seconds,
        ]);
    }

    /**
     * Log rate limit hits for monitoring.
     */
    protected function logRateLimitHit(Request $request, string $key, string $tier, int $limit): void
    {
        // Only log a sample of hits to avoid overwhelming the database
        if (rand(1, 100) <= 5) { // Log 5% of hits
            ApiRateLimit::create([
                'identifier' => $request->user() ? $request->user()->id : $request->ip(),
                'identifier_type' => $request->user() ? 'user' : 'ip',
                'endpoint' => $request->route()->getName(),
                'tier' => $tier,
                'requests_count' => RateLimiter::attempts($key),
                'limit' => $limit,
                'window_start' => now()->startOfMinute(),
                'window_end' => now()->endOfMinute(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                ],
            ]);
        }
    }
}
