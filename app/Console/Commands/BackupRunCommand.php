<?php

namespace App\Console\Commands;

use App\Models\BackupMonitoring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run-custom 
                            {--type=daily : Backup type (daily, weekly, monthly, manual)}
                            {--disk= : Specific disk to backup to}
                            {--disable-notifications : Disable backup notifications}';

    protected $description = 'Run backup with monitoring and logging';

    public function handle()
    {
        $type = $this->option('type');
        $disk = $this->option('disk');
        $disableNotifications = $this->option('disable-notifications');

        $this->info("Starting {$type} backup...");

        // Create backup monitoring record
        $backup = BackupMonitoring::create([
            'backup_type' => $type,
            'status' => 'started',
            'disk' => $disk ?? implode(',', config('backup.backup.destination.disks')),
            'started_at' => now(),
            'metadata' => [
                'command' => 'backup:run-custom',
                'initiated_by' => 'system',
                'options' => [
                    'type' => $type,
                    'disk' => $disk,
                    'disable_notifications' => $disableNotifications,
                ],
            ],
        ]);

        try {
            // Prepare backup command arguments
            $arguments = [];
            
            if ($disk) {
                $arguments['--only-to-disk'] = $disk;
            }

            if ($disableNotifications) {
                $arguments['--disable-notifications'] = true;
            }

            // Run backup command
            $startTime = microtime(true);
            $exitCode = Artisan::call('backup:run', $arguments);
            $duration = round(microtime(true) - $startTime, 2);

            if ($exitCode === 0) {
                // Get backup info
                $backupFiles = [];
                $totalSize = 0;
                $disks = $disk ? [$disk] : config('backup.backup.destination.disks');

                foreach ($disks as $diskName) {
                    try {
                        $backupDestination = BackupDestination::create($diskName, config('backup.backup.name'));
                        $files = $backupDestination->backups();
                        
                        if ($files->isNotEmpty()) {
                            $latestBackup = $files->first();
                            $backupFiles[] = $latestBackup->path();
                            $totalSize += $latestBackup->size();
                        }
                    } catch (\Exception $e) {
                        $this->error("Error getting backup info from disk {$diskName}: " . $e->getMessage());
                    }
                }

                $backup->update([
                    'status' => 'completed',
                    'path' => $backupFiles[0] ?? null,
                    'size_bytes' => $totalSize,
                    'files' => $backupFiles,
                    'duration_seconds' => $duration,
                    'completed_at' => now(),
                ]);

                $this->info("Backup completed successfully.");
                $this->info("Duration: {$duration} seconds");
                $this->info("Size: " . $this->formatBytes($totalSize));
                $this->info("Files: " . implode(', ', $backupFiles));

                return Command::SUCCESS;

            } else {
                $output = Artisan::output();
                
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $output,
                    'duration_seconds' => $duration,
                    'completed_at' => now(),
                ]);

                $this->error("Backup failed.");
                $this->error("Duration: {$duration} seconds");
                $this->error("Error: " . $output);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->error("Backup failed with exception: " . $e->getMessage());
            return Command::FAILURE;
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
