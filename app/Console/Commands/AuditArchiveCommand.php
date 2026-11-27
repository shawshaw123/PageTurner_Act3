<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuditArchiveCommand extends Command
{
    protected $signature = 'audit:archive {--months=12 : Archive audits older than specified months} {--dry-run : Show what would be archived without actually archiving}';
    protected $description = 'Archive old audit logs for long-term storage';

    public function handle()
    {
        $months = $this->option('months');
        $dryRun = $this->option('dry-run');
        
        $this->info("Starting audit archiving for records older than {$months} months...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No records will be actually archived');
        }

        try {
            $cutoffDate = now()->subMonths($months);
            
            // Check if audit_logs table exists
            if (!DB::getSchemaBuilder()->hasTable('audits')) {
                $this->warn('Audit logs table not found. Skipping archiving.');
                return Command::SUCCESS;
            }

            // Get old audit records
            $oldAudits = DB::table('audits')
                          ->where('created_at', '<', $cutoffDate)
                          ->orderBy('created_at')
                          ->get();

            $count = $oldAudits->count();
            
            if ($count === 0) {
                $this->info('No audit records found older than ' . $cutoffDate->format('Y-m-d'));
                return Command::SUCCESS;
            }

            $this->info("Found {$count} audit records older than {$cutoffDate->format('Y-m-d')}");

            if (!$dryRun) {
                // Create archive directory
                $archiveDir = 'audit-archives/' . now()->format('Y/m');
                $this->ensureDirectoryExists($archiveDir);

                // Create archive file
                $archiveFile = $archiveDir . '/audit_archive_' . now()->format('Y-m-d_H-i-s') . '.json';
                $archivePath = storage_path('app/' . $archiveFile);

                // Prepare archive data
                $archiveData = [
                    'archived_at' => now()->toISOString(),
                    'cutoff_date' => $cutoffDate->toISOString(),
                    'total_records' => $count,
                    'records' => $oldAudits->map(function ($audit) {
                        return [
                            'id' => $audit->id,
                            'user_id' => $audit->user_id,
                            'event' => $audit->event,
                            'auditable_type' => $audit->auditable_type,
                            'auditable_id' => $audit->auditable_id,
                            'old_values' => json_decode($audit->old_values, true),
                            'new_values' => json_decode($audit->new_values, true),
                            'url' => $audit->url,
                            'ip_address' => $audit->ip_address,
                            'user_agent' => $audit->user_agent,
                            'created_at' => $audit->created_at,
                        ];
                    })->toArray()
                ];

                // Write archive file
                file_put_contents($archivePath, json_encode($archiveData, JSON_PRETTY_PRINT));

                // Delete old records from database
                $deleted = DB::table('audits')
                            ->where('created_at', '<', $cutoffDate)
                            ->delete();

                // Create archive log entry
                DB::table('audit_archives')->insert([
                    'archive_file' => $archiveFile,
                    'records_count' => $count,
                    'cutoff_date' => $cutoffDate,
                    'archived_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("Successfully archived {$deleted} audit records");
                $this->info("Archive file: {$archiveFile}");
                $this->info("Archive size: " . $this->formatBytes(filesize($archivePath)));

                Log::info('Audit archiving completed', [
                    'records_archived' => $deleted,
                    'archive_file' => $archiveFile,
                    'cutoff_date' => $cutoffDate->format('Y-m-d'),
                ]);

            } else {
                $this->info("Would archive {$count} audit records in dry run mode");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to archive audit records: ' . $e->getMessage());
            Log::error('Audit archiving failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }

    protected function ensureDirectoryExists($directory)
    {
        $fullPath = storage_path('app/' . $directory);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
