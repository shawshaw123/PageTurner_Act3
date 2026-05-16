<?php

namespace App\Console\Commands;

use App\Models\BackupMonitoring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Spatie\Backup\Tasks\Backup\BackupDestination;

class RunBackup extends Command
{
    protected $signature = 'backup:run-custom {--type=daily}';
    protected $description = 'Run custom backup with monitoring';

    public function handle()
    {
        $type = $this->option('type');
        
        // Create monitoring record
        $monitoring = BackupMonitoring::create([
            'backup_type' => $type,
            'status' => 'started',
            'disk' => 'local',
            'started_at' => now(),
        ]);

        try {
            $startTime = now();
            
            // Run the backup
            $this->info("Starting {$type} backup...");
            
            $backupJob = new BackupJob(config('backup.backup'));
            $backupJob->run();
            
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            // Get backup info
            $backupDestination = BackupDestination::create('local', 'pageturner');
            $backup = $backupDestination->newestBackup();
            
            // Update monitoring record
            $monitoring->update([
                'status' => 'completed',
                'completed_at' => $endTime,
                'duration_seconds' => $duration,
                'size_bytes' => $backup ? $backup->size() : null,
                'path' => $backup ? $backup->path() : null,
                'files' => $backup ? [
                    'database' => true,
                    'files' => true,
                    'size' => $backup->size(),
                ] : null,
            ]);

            $this->info("Backup completed successfully in {$duration} seconds");
            $this->info("Backup size: " . ($backup ? $this->formatBytes($backup->size()) : 'Unknown'));
            
            // Send notification
            $this->sendSuccessNotification($monitoring);
            
            return 0;
            
        } catch (\Exception $e) {
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            $monitoring->update([
                'status' => 'failed',
                'completed_at' => $endTime,
                'duration_seconds' => $duration,
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Backup failed: " . $e->getMessage());
            
            // Send failure notification
            $this->sendFailureNotification($monitoring, $e);
            
            return 1;
        }
    }

    protected function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    protected function sendSuccessNotification(BackupMonitoring $monitoring)
    {
        try {
            // Send email notification to admin
            \Mail::to('admin@pageturner.com')->send(new \App\Mail\BackupSuccessMail($monitoring));
        } catch (\Exception $e) {
            \Log::error('Failed to send backup success notification: ' . $e->getMessage());
        }
    }

    protected function sendFailureNotification(BackupMonitoring $monitoring, \Exception $exception)
    {
        try {
            // Send email notification to admin
            \Mail::to('admin@pageturner.com')->send(new \App\Mail\BackupFailureMail($monitoring, $exception));
        } catch (\Exception $e) {
            \Log::error('Failed to send backup failure notification: ' . $e->getMessage());
        }
    }
}
