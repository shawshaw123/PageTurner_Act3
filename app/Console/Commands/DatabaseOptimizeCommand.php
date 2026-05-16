<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseOptimizeCommand extends Command
{
    protected $signature = 'db:optimize {--tables=* : Specific tables to optimize}';
    protected $description = 'Optimize database tables for better performance';

    public function handle()
    {
        $this->info('Starting database optimization...');

        try {
            $tables = $this->option('tables');
            
            if (empty($tables)) {
                // Get all tables
                $tables = DB::select('SHOW TABLES');
                $tables = array_map('current', $tables);
            }

            $this->info('Optimizing tables: ' . implode(', ', $tables));

            $optimizedTables = 0;
            $errors = [];

            foreach ($tables as $table) {
                try {
                    $this->line("Optimizing table: {$table}");

                    // Analyze table
                    DB::statement("ANALYZE TABLE `{$table}`");
                    
                    // Optimize table (MySQL specific)
                    if (DB::getDriverName() === 'mysql') {
                        DB::statement("OPTIMIZE TABLE `{$table}`");
                    }

                    $optimizedTables++;
                    $this->info("✓ {$table} optimized");

                } catch (\Exception $e) {
                    $errors[] = "Failed to optimize {$table}: " . $e->getMessage();
                    $this->error("✗ Failed to optimize {$table}: " . $e->getMessage());
                }
            }

            // Clear query cache
            try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement('RESET QUERY CACHE');
                    $this->info('✓ Query cache cleared');
                }
            } catch (\Exception $e) {
                $this->warn('Could not clear query cache: ' . $e->getMessage());
            }

            $this->newLine();
            $this->info('Database optimization completed');
            $this->info("Tables optimized: {$optimizedTables}");
            
            if (!empty($errors)) {
                $this->error('Errors encountered:');
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
            }

            Log::info('Database optimization completed', [
                'tables_optimized' => $optimizedTables,
                'total_tables' => count($tables),
                'errors' => count($errors),
            ]);

            return empty($errors) ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('Database optimization failed: ' . $e->getMessage());
            Log::error('Database optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}
