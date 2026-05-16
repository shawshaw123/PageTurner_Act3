<?php

namespace App\Console\Commands;

use App\Models\ExportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportCleanupCommand extends Command
{
    protected $signature = 'export:cleanup-expired {--force : Force cleanup without confirmation}';
    protected $description = 'Clean up expired export files and records';

    public function handle()
    {
        $this->info('Starting cleanup of expired exports...');

        if (!$this->option('force')) {
            if (!$this->confirm('This will remove expired export files and records. Continue?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            // Find expired exports
            $expiredExports = ExportLog::where('expires_at', '<', now())->get();
            
            $count = $expiredExports->count();
            $filesDeleted = 0;
            $recordsDeleted = 0;

            if ($count === 0) {
                $this->info('No expired exports found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$count} expired exports:");

            foreach ($expiredExports as $export) {
                $this->line("Export ID: {$export->id} - Model: {$export->model_type} - Format: {$export->format} - Expired: {$export->expires_at->format('Y-m-d H:i:s')}");

                // Delete file if exists
                if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
                    Storage::disk('local')->delete($export->file_path);
                    $filesDeleted++;
                    $this->line("  - Deleted file: {$export->file_path}");
                }

                // Delete the export record
                $export->delete();
                $recordsDeleted++;

                Log::info('Expired export cleaned up', [
                    'export_id' => $export->id,
                    'model_type' => $export->model_type,
                    'file_path' => $export->file_path,
                    'expired_at' => $export->expires_at,
                ]);
            }

            $this->info("Cleanup completed successfully.");
            $this->info("Files deleted: {$filesDeleted}");
            $this->info("Records deleted: {$recordsDeleted}");

            Log::info('Expired exports cleanup completed', [
                'total_expired' => $count,
                'files_deleted' => $filesDeleted,
                'records_deleted' => $recordsDeleted,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired exports: ' . $e->getMessage());
            Log::error('Expired exports cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}
