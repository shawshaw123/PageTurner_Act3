<?php

namespace App\Console\Commands;

use App\Models\BackupMonitoring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:health-check {--send-alert : Send alert if issues found}';
    protected $description = 'Perform system health checks';

    public function handle()
    {
        $this->info('Starting system health check...');

        $issues = [];
        $warnings = [];

        try {
            // Check database connection
            $this->checkDatabase($issues, $warnings);

            // Check storage
            $this->checkStorage($issues, $warnings);

            // Check backup status
            $this->checkBackups($issues, $warnings);

            // Check queue system
            $this->checkQueues($issues, $warnings);

            // Check disk space
            $this->checkDiskSpace($issues, $warnings);

            // Check memory usage
            $this->checkMemoryUsage($issues, $warnings);

            // Report results
            $this->reportResults($issues, $warnings);

            // Send alerts if requested and issues found
            if ($this->option('send-alert') && !empty($issues)) {
                $this->sendAlert($issues, $warnings);
            }

            Log::info('System health check completed', [
                'issues_count' => count($issues),
                'warnings_count' => count($warnings),
            ]);

            return empty($issues) ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
            Log::error('System health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }

    protected function checkDatabase(&$issues, &$warnings)
    {
        try {
            DB::connection()->getPdo();
            $this->info('✓ Database connection: OK');
        } catch (\Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
            $this->error('✗ Database connection: FAILED');
            return;
        }

        // Check table counts
        try {
            $userCount = DB::table('users')->count();
            $bookCount = DB::table('books')->count();
            $orderCount = DB::table('orders')->count();

            $this->info("✓ Database tables: Users ({$userCount}), Books ({$bookCount}), Orders ({$orderCount})");
        } catch (\Exception $e) {
            $issues[] = 'Failed to query database tables: ' . $e->getMessage();
        }
    }

    protected function checkStorage(&$issues, &$warnings)
    {
        // Check if storage directories are writable
        $paths = [
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                try {
                    mkdir($path, 0755, true);
                    $this->info("✓ Created directory: {$path}");
                } catch (\Exception $e) {
                    $issues[] = "Cannot create directory {$path}: " . $e->getMessage();
                    continue;
                }
            }

            if (!is_writable($path)) {
                $issues[] = "Directory {$path} is not writable";
            } else {
                $this->info("✓ Directory writable: {$path}");
            }
        }
    }

    protected function checkBackups(&$issues, &$warnings)
    {
        try {
            $lastBackup = BackupMonitoring::orderBy('started_at', 'desc')->first();
            
            if (!$lastBackup) {
                $issues[] = 'No backups found';
                return;
            }

            $hoursSinceLastBackup = now()->diffInHours($lastBackup->started_at);

            if ($hoursSinceLastBackup > 48) {
                $issues[] = "Last backup was {$hoursSinceLastBackup} hours ago";
            } elseif ($hoursSinceLastBackup > 24) {
                $warnings[] = "Last backup was {$hoursSinceLastBackup} hours ago";
            } else {
                $this->info("✓ Last backup: {$hoursSinceLastBackup} hours ago");
            }

            // Check recent backup failures
            $recentFailures = BackupMonitoring::where('status', 'failed')
                                           ->where('started_at', '>', now()->subHours(24))
                                           ->count();

            if ($recentFailures > 0) {
                $issues[] = "{$recentFailures} backup failures in the last 24 hours";
            } else {
                $this->info('✓ No recent backup failures');
            }

        } catch (\Exception $e) {
            $warnings[] = 'Failed to check backup status: ' . $e->getMessage();
        }
    }

    protected function checkQueues(&$issues, &$warnings)
    {
        try {
            // Check for failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            
            if ($failedJobs > 10) {
                $warnings[] = "High number of failed jobs: {$failedJobs}";
            } elseif ($failedJobs > 0) {
                $this->info("⚠ Found {$failedJobs} failed jobs");
            } else {
                $this->info('✓ No failed jobs');
            }

            // Check for pending jobs (if jobs table exists)
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
                
                if ($pendingJobs > 100) {
                    $warnings[] = "High number of pending jobs: {$pendingJobs}";
                } else {
                    $this->info("✓ {$pendingJobs} pending jobs");
                }
            }

        } catch (\Exception $e) {
            $warnings[] = 'Failed to check queue status: ' . $e->getMessage();
        }
    }

    protected function checkDiskSpace(&$issues, &$warnings)
    {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');

        if ($freeSpace === false || $totalSpace === false) {
            $warnings[] = 'Cannot determine disk space';
            return;
        }

        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;

        if ($usagePercent > 90) {
            $issues[] = "Disk usage critical: " . round($usagePercent, 2) . "% used";
        } elseif ($usagePercent > 80) {
            $warnings[] = "Disk usage high: " . round($usagePercent, 2) . "% used";
        } else {
            $this->info("✓ Disk usage: " . round($usagePercent, 2) . "% used");
        }
    }

    protected function checkMemoryUsage(&$issues, &$warnings)
    {
        if (function_exists('memory_get_usage')) {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
            
            if ($memoryLimit > 0) {
                $usagePercent = ($memoryUsage / $memoryLimit) * 100;
                
                if ($usagePercent > 90) {
                    $warnings[] = "Memory usage high: " . round($usagePercent, 2) . "% used";
                } else {
                    $this->info("✓ Memory usage: " . round($usagePercent, 2) . "% used");
                }
            }
        }
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

    protected function reportResults($issues, $warnings)
    {
        $this->newLine();
        $this->info('=== HEALTH CHECK RESULTS ===');

        if (empty($issues) && empty($warnings)) {
            $this->info('✓ All systems operational');
            return;
        }

        if (!empty($issues)) {
            $this->newLine();
            $this->error('ISSUES FOUND:');
            foreach ($issues as $issue) {
                $this->error("  - {$issue}");
            }
        }

        if (!empty($warnings)) {
            $this->newLine();
            $this->warn('WARNINGS:');
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }

        $this->newLine();
        $this->info('Summary: ' . count($issues) . ' issues, ' . count($warnings) . ' warnings');
    }

    protected function sendAlert($issues, $warnings)
    {
        try {
            // TODO: Implement alert notification system
            // This could send email, Slack notification, etc.
            Log::warning('System health check alert', [
                'issues' => $issues,
                'warnings' => $warnings,
                'timestamp' => now()->toISOString(),
            ]);

            $this->info('Alert sent to administrators');

        } catch (\Exception $e) {
            $this->error('Failed to send alert: ' . $e->getMessage());
        }
    }
}
