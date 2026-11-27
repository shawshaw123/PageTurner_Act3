<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Models\ExportLog;
use App\Models\BackupMonitoring;
use App\Models\ApiRateLimit;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ApiRateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get recent data management activities
        $recentImports = ImportLog::with('user')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

        $recentExports = ExportLog::with('user')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

        // Backup status
        $lastBackup = BackupMonitoring::orderBy('started_at', 'desc')->first();
        $backupStats = [
            'total_backups' => BackupMonitoring::count(),
            'successful_backups' => BackupMonitoring::where('status', 'completed')->count(),
            'failed_backups' => BackupMonitoring::where('status', 'failed')->count(),
            'last_backup' => $lastBackup,
            'success_rate' => $this->calculateBackupSuccessRate(),
        ];

        // System health
        $systemHealth = $this->getSystemHealth();

        // API usage statistics
        $apiStats = ApiRateLimitService::getDashboardData();

        // Recent audit events
        $recentAudits = \OwenIt\Auditing\Models\Audit::with('user')
                                                     ->orderBy('created_at', 'desc')
                                                     ->limit(10)
                                                     ->get();

        // Audit statistics
        $auditStats = AuditService::getStatistics(24);

        // Queue statistics
        $queueStats = $this->getQueueStats();

        // System statistics
        $stats = [
            'total_books' => Book::count(),
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_books' => Book::where('stock_quantity', '<', 10)->count(),
            'out_of_stock_books' => Book::where('stock_quantity', 0)->count(),
        ];

        return view('admin.dashboard.index', compact(
            'recentImports',
            'recentExports',
            'backupStats',
            'systemHealth',
            'apiStats',
            'recentAudits',
            'stats',
            'auditStats',
            'queueStats'
        ));
    }

    protected function calculateBackupSuccessRate()
    {
        $totalBackups = BackupMonitoring::count();
        $successfulBackups = BackupMonitoring::where('status', 'completed')->count();
        
        if ($totalBackups > 0) {
            return round(($successfulBackups / $totalBackups) * 100, 2);
        }
        
        return 0;
    }

    protected function getQueueStats()
    {
        $stats = [
            'failed_jobs' => 0,
            'pending_jobs' => 0,
            'processed_jobs' => 0,
        ];

        try {
            // Check if failed_jobs table exists
            if (\Schema::hasTable('failed_jobs')) {
                $stats['failed_jobs'] = \DB::table('failed_jobs')->count();
            }
            
            // Check if jobs table exists
            if (\Schema::hasTable('jobs')) {
                $stats['pending_jobs'] = \DB::table('jobs')->count();
                $stats['processed_jobs'] = \DB::table('jobs')
                    ->where('completed_at', '!=', null)
                    ->count();
            }
        } catch (\Exception $e) {
            // Handle error gracefully
        }

        return $stats;
    }

    public function dataManagement()
    {
        // Import statistics
        $importStats = [
            'total_imports' => ImportLog::count(),
            'successful_imports' => ImportLog::where('status', 'completed')->count(),
            'failed_imports' => ImportLog::where('status', 'failed')->count(),
            'processing_imports' => ImportLog::where('status', 'processing')->count(),
        ];

        // Export statistics
        $exportStats = [
            'total_exports' => ExportLog::count(),
            'completed_exports' => ExportLog::where('status', 'completed')->count(),
            'failed_exports' => ExportLog::where('status', 'failed')->count(),
            'processing_exports' => ExportLog::where('status', 'processing')->count(),
        ];

        return view('admin.dashboard.data-management', compact(
            'importStats',
            'exportStats',
            'queueStats'
        ));
    }

    public function systemMonitoring()
    {
        // Backup monitoring
        $backupMonitoring = [
            'recent_backups' => BackupMonitoring::orderBy('started_at', 'desc')->limit(10)->get(),
            'backup_health' => $this->getBackupHealth(),
            'storage_usage' => $this->getStorageUsage(),
        ];

        // System health
        $systemHealth = $this->getSystemHealth();

        // API monitoring
        $apiMonitoring = [
            'current_stats' => ApiRateLimitService::getStatistics(1), // Last hour
            'alerts' => ApiRateLimitService::detectSuspiciousActivity(),
            'top_consumers' => ApiRateLimit::with('user')
                                       ->where('window_start', '>=', now()->subHour())
                                       ->selectRaw('identifier, SUM(requests_count) as total_requests')
                                       ->groupBy('identifier')
                                       ->orderBy('total_requests', 'desc')
                                       ->limit(10)
                                       ->get(),
        ];

        // Audit monitoring
        $auditMonitoring = [
            'recent_events' => \OwenIt\Auditing\Models\Audit::with('user')
                                                             ->orderBy('created_at', 'desc')
                                                             ->limit(20)
                                                             ->get(),
            'statistics' => AuditService::getStatistics(24),
            'security_alerts' => AuditService::detectSuspiciousActivity(),
        ];

        return view('admin.dashboard.system-monitoring', compact(
            'backupMonitoring',
            'systemHealth',
            'apiMonitoring',
            'auditMonitoring'
        ));
    }

    protected function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Database check
        try {
            \DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            $health['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed',
            ];
            $health['status'] = 'critical';
        }

        // Storage check
        $storagePath = storage_path();
        if (is_writable($storagePath)) {
            $health['checks']['storage'] = [
                'status' => 'ok',
                'message' => 'Storage is writable',
            ];
        } else {
            $health['checks']['storage'] = [
                'status' => 'error',
                'message' => 'Storage is not writable',
            ];
            $health['status'] = 'critical';
        }

        // Disk space check
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        if ($freeSpace && $totalSpace) {
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            if ($usagePercent > 90) {
                $health['checks']['disk_space'] = [
                    'status' => 'critical',
                    'message' => 'Disk usage critical: ' . round($usagePercent, 2) . '%',
                ];
                $health['status'] = 'critical';
            } elseif ($usagePercent > 80) {
                $health['checks']['disk_space'] = [
                    'status' => 'warning',
                    'message' => 'Disk usage high: ' . round($usagePercent, 2) . '%',
                ];
                if ($health['status'] === 'healthy') {
                    $health['status'] = 'warning';
                }
            } else {
                $health['checks']['disk_space'] = [
                    'status' => 'ok',
                    'message' => 'Disk usage: ' . round($usagePercent, 2) . '%',
                ];
            }
        }

        // Memory check
        if (function_exists('memory_get_usage')) {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
            
            if ($memoryLimit > 0) {
                $usagePercent = ($memoryUsage / $memoryLimit) * 100;
                
                if ($usagePercent > 90) {
                    $health['checks']['memory'] = [
                        'status' => 'warning',
                        'message' => 'Memory usage high: ' . round($usagePercent, 2) . '%',
                    ];
                    if ($health['status'] === 'healthy') {
                        $health['status'] = 'warning';
                    }
                } else {
                    $health['checks']['memory'] = [
                        'status' => 'ok',
                        'message' => 'Memory usage: ' . round($usagePercent, 2) . '%',
                    ];
                }
            }
        }

        return $health;
    }

    protected function getBackupHealth(): array
    {
        $health = [
            'status' => 'unknown',
            'last_successful' => null,
            'last_failed' => null,
            'success_rate' => 0,
        ];

        $recentBackups = BackupMonitoring::where('started_at', '>=', now()->subDays(7))->get();
        
        if ($recentBackups->isEmpty()) {
            $health['status'] = 'warning';
            $health['message'] = 'No backups in the last 7 days';
            return $health;
        }

        $successfulBackups = $recentBackups->where('status', 'completed');
        $failedBackups = $recentBackups->where('status', 'failed');

        $health['last_successful'] = $successfulBackups->first();
        $health['last_failed'] = $failedBackups->first();
        $health['success_rate'] = $recentBackups->count() > 0 ? 
            ($successfulBackups->count() / $recentBackups->count()) * 100 : 0;

        if ($health['success_rate'] >= 90) {
            $health['status'] = 'healthy';
        } elseif ($health['success_rate'] >= 70) {
            $health['status'] = 'warning';
        } else {
            $health['status'] = 'critical';
        }

        return $health;
    }

    protected function getStorageUsage(): array
    {
        $usage = [
            'total_space' => 0,
            'used_space' => 0,
            'free_space' => 0,
            'usage_percent' => 0,
        ];

        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');

        if ($totalSpace && $freeSpace) {
            $usage['total_space'] = $totalSpace;
            $usage['free_space'] = $freeSpace;
            $usage['used_space'] = $totalSpace - $freeSpace;
            $usage['usage_percent'] = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        }

        return $usage;
    }

    protected function parseMemoryLimit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    public function apiStats(Request $request)
    {
        $hours = $request->get('hours', 24);
        $statistics = ApiRateLimitService::getStatistics($hours);
        
        return response()->json($statistics);
    }

    public function auditStats(Request $request)
    {
        $days = $request->get('days', 30);
        $statistics = AuditService::getStatistics($days);
        
        return response()->json($statistics);
    }

    public function refreshSystemHealth()
    {
        $health = $this->getSystemHealth();
        
        return response()->json($health);
    }
}
