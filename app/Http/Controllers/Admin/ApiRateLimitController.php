<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRateLimit;
use App\Services\ApiRateLimitService;
use Illuminate\Http\Request;

class ApiRateLimitController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiRateLimit::with('user');

        // Filter by identifier type
        if ($request->filled('identifier_type')) {
            $query->where('identifier_type', $request->identifier_type);
        }

        // Filter by tier
        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        // Filter by endpoint
        if ($request->filled('endpoint')) {
            $query->where('endpoint', $request->endpoint);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('window_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('window_start', '<=', $request->date_to);
        }

        // Filter by rate limit hits
        if ($request->filled('show_hits_only')) {
            $query->whereRaw('requests_count >= limit');
        }

        $rateLimits = $query->orderBy('window_start', 'desc')->paginate(50);

        return view('admin.api-rate-limits.index', compact('rateLimits'));
    }

    public function dashboard()
    {
        $data = ApiRateLimitService::getDashboardData();
        $statistics = ApiRateLimitService::getStatistics(24);
        $alerts = ApiRateLimitService::detectSuspiciousActivity();

        return view('admin.api-rate-limits.dashboard', compact('data', 'statistics', 'alerts'));
    }

    public function show($id)
    {
        $rateLimit = ApiRateLimit::with('user')->findOrFail($id);
        return view('admin.api-rate-limits.show', compact('rateLimit'));
    }

    public function statistics(Request $request)
    {
        $hours = $request->get('hours', 24);
        $statistics = ApiRateLimitService::getStatistics($hours);

        return response()->json($statistics);
    }

    public function userStatus(Request $request, $userId)
    {
        $status = ApiRateLimitService::getCurrentStatus($userId, 'user');
        return response()->json($status);
    }

    public function ipStatus(Request $request, $ip)
    {
        $status = ApiRateLimitService::getCurrentStatus($ip, 'ip');
        return response()->json($status);
    }

    public function clearUserLimit(Request $request, $userId)
    {
        ApiRateLimitService::clearRateLimit($userId, 'user');
        
        // Log the action
        \App\Services\AuditService::logSecurity('rate_limit_cleared', 'Rate limit cleared for user', [
            'user_id' => $userId,
            'cleared_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'Rate limit cleared for user.');
    }

    public function clearIpLimit(Request $request, $ip)
    {
        ApiRateLimitService::clearRateLimit($ip, 'ip');
        
        // Log the action
        \App\Services\AuditService::logSecurity('rate_limit_cleared', 'Rate limit cleared for IP', [
            'ip_address' => $ip,
            'cleared_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'Rate limit cleared for IP address.');
    }

    public function setCustomLimit(Request $request, $userId)
    {
        $request->validate([
            'limit' => 'required|integer|min:1|max:10000',
            'duration_minutes' => 'required|integer|min:1|max:1440', // Max 24 hours
        ]);

        ApiRateLimitService::setCustomLimit(
            $userId, 
            $request->limit, 
            $request->duration_minutes
        );
        
        // Log the action
        \App\Services\AuditService::logSecurity('custom_rate_limit_set', 'Custom rate limit set for user', [
            'user_id' => $userId,
            'limit' => $request->limit,
            'duration_minutes' => $request->duration_minutes,
            'set_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'Custom rate limit set for user.');
    }

    public function blockUser(Request $request, $userId)
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:1|max:1440', // Max 24 hours
        ]);

        ApiRateLimitService::blockIdentifier($userId, 'user', $request->duration_minutes);
        
        // Log the action
        \App\Services\AuditService::logSecurity('user_blocked', 'User blocked from API', [
            'user_id' => $userId,
            'duration_minutes' => $request->duration_minutes,
            'blocked_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'User blocked from API access.');
    }

    public function blockIp(Request $request, $ip)
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:1|max:1440', // Max 24 hours
        ]);

        ApiRateLimitService::blockIdentifier($ip, 'ip', $request->duration_minutes);
        
        // Log the action
        \App\Services\AuditService::logSecurity('ip_blocked', 'IP blocked from API', [
            'ip_address' => $ip,
            'duration_minutes' => $request->duration_minutes,
            'blocked_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'IP address blocked from API access.');
    }

    public function unblockUser(Request $request, $userId)
    {
        $key = "api:block:user:{$userId}";
        \Cache::forget($key);
        
        // Log the action
        \App\Services\AuditService::logSecurity('user_unblocked', 'User unblocked from API', [
            'user_id' => $userId,
            'unblocked_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'User unblocked from API access.');
    }

    public function unblockIp(Request $request, $ip)
    {
        $key = "api:block:ip:{$ip}";
        \Cache::forget($key);
        
        // Log the action
        \App\Services\AuditService::logSecurity('ip_unblocked', 'IP unblocked from API', [
            'ip_address' => $ip,
            'unblocked_by' => auth()->user()->id,
        ]);

        return back()->with('success', 'IP address unblocked from API access.');
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $query = ApiRateLimit::with('user')
                           ->whereDate('window_start', '>=', $request->date_from)
                           ->whereDate('window_start', '<=', $request->date_to);

        // Apply filters
        if ($request->filled('identifier_type')) {
            $query->where('identifier_type', $request->identifier_type);
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        $rateLimits = $query->orderBy('window_start')->get();

        if ($request->format === 'csv') {
            return $this->exportCsv($rateLimits);
        } else {
            return $this->exportExcel($rateLimits);
        }
    }

    protected function exportCsv($rateLimits)
    {
        $filename = 'api_rate_limits_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rateLimits) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID',
                'Identifier',
                'Identifier Type',
                'Endpoint',
                'Tier',
                'Requests Count',
                'Limit',
                'Usage %',
                'IP Address',
                'User Agent',
                'Window Start',
                'Created At',
            ]);

            // Data rows
            foreach ($rateLimits as $rateLimit) {
                $usagePercent = $rateLimit->limit > 0 ? 
                    round(($rateLimit->requests_count / $rateLimit->limit) * 100, 2) : 0;

                fputcsv($file, [
                    $rateLimit->id,
                    $rateLimit->identifier,
                    $rateLimit->identifier_type,
                    $rateLimit->endpoint,
                    $rateLimit->tier,
                    $rateLimit->requests_count,
                    $rateLimit->limit,
                    $usagePercent . '%',
                    $rateLimit->ip_address,
                    $rateLimit->user_agent,
                    $rateLimit->window_start->format('Y-m-d H:i:s'),
                    $rateLimit->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportExcel($rateLimits)
    {
        // Implementation for Excel export using Laravel Excel
        // This would require creating an Export class similar to the ones we created earlier
        return response()->json(['message' => 'Excel export not implemented yet']);
    }

    public function cleanup()
    {
        // Clean up old rate limit records (older than 30 days)
        $deleted = ApiRateLimit::where('window_start', '<', now()->subDays(30))->delete();
        
        return back()->with('success', "Cleaned up {$deleted} old rate limit records.");
    }
}
