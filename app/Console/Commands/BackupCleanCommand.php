<?php

namespace App\Console\Commands;

use App\Models\BackupMonitoring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupCleanCommand extends Command
{
    protected $signature = 'backup:clean-custom {--force : Force cleanup without confirmation}';
    protected $description = 'Clean old backups with monitoring';

    public function handle()
    {
        $this->info('Starting backup cleanup...');

        if (!$this->option('force')) {
            if (!$this->confirm('This will remove old backups according to the retention policy. Continue?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            $startTime = microtime(true);
            $exitCode = Artisan::call('backup:clean');
            $duration = round(microtime(true) - $startTime, 2);

            if ($exitCode === 0) {
                $this->info('Backup cleanup completed successfully.');
                $this->info("Duration: {$duration} seconds");
                $this->info('Output: ' . Artisan::output());
                
                return Command::SUCCESS;
            } else {
                $this->error('Backup cleanup failed.');
                $this->error('Output: ' . Artisan::output());
                
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Backup cleanup failed with exception: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
