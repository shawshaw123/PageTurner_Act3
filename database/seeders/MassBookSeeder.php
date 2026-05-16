<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MassBookSeeder extends Seeder
{
    /**
     * Optimal chunk size for MySQL/PostgreSQL bulk inserts
     */
    private const CHUNK_SIZE = 5000;
    
    /**
     * Total number of records to generate
     */
    private const TOTAL_RECORDS = 1000000;
    
    /**
     * Memory limit in MB for monitoring
     */
    private const MEMORY_LIMIT_MB = 512;

    public function run(): void
    {
        $this->command->info('🚀 Starting Mass Book Seeding...');
        $this->command->info("Target: " . number_format(self::TOTAL_RECORDS) . " records");
        $this->command->info("Chunk size: " . self::CHUNK_SIZE . " records per batch");
        
        $startTime = microtime(true);
        $inserted = 0;
        $batchCount = 0;
        
        // Clear existing books to start fresh (handle foreign key constraints)
        $this->command->info('🗑️  Clearing existing books...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            DB::table('books')->truncate();
        } catch (\Exception $e) {
            // If truncate fails due to constraints, use delete instead
            $this->command->warn('Truncate failed, using delete instead...');
            DB::table('books')->delete();
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Disable query logging for performance
        DB::disableQueryLog();
        
        $this->command->info('📦 Generating books in chunks...');
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            try {
                // Generate batch using make() - does NOT persist models (memory efficient)
                $factory = Book::factory()->count($batchSize);
                
                // Apply states with proper Laravel syntax
                $books = [];
                for ($i = 0; $i < $batchSize; $i++) {
                    $rand = mt_rand(1, 100);
                    
                    if ($rand <= 10) {  // 10% bestsellers
                        $books[] = $factory->bestseller()->make()->toArray();
                    } elseif ($rand <= 15) {  // 5% rare books
                        $books[] = $factory->rare()->make()->toArray();
                    } elseif ($rand <= 30) {  // 15% new releases
                        $books[] = $factory->newRelease()->make()->toArray();
                    } elseif ($rand <= 38) {  // 8% academic books
                        $books[] = $factory->academic()->make()->toArray();
                    } else {  // 62% regular books
                        $books[] = $factory->make()->toArray();
                    }
                }
                
                // Raw batch insert for maximum throughput
                DB::table('books')->insert($books);
                
                $inserted += $batchSize;
                $batchCount++;
                
                // Calculate batch performance
                $batchTime = microtime(true) - $batchStart;
                $batchRate = $batchSize / $batchTime;
                $overallRate = $inserted / (microtime(true) - $startTime);
                $progress = ($inserted / self::TOTAL_RECORDS) * 100;
                
                // Memory monitoring
                $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
                $memoryPercent = ($memoryUsage / self::MEMORY_LIMIT_MB) * 100;
                
                // Progress bar
                $this->command->line(
                    sprintf(
                        "[%s] Batch %d: %d records (%.1f%%) | %.0f rec/s | Memory: %.1f MB (%.0f%%)",
                        str_repeat('=', (int)($progress / 2)) . str_repeat(' ', 50 - (int)($progress / 2)),
                        $batchCount,
                        $inserted,
                        $progress,
                        $overallRate,
                        $memoryUsage,
                        $memoryPercent
                    )
                );
                
                // Force garbage collection every 10 chunks to prevent memory buildup
                if ($batchCount % 10 === 0) {
                    unset($books);
                    gc_collect_cycles();
                    
                    $this->command->info("🧹 Garbage collection triggered");
                    
                    // Check memory usage
                    if ($memoryUsage > self::MEMORY_LIMIT_MB * 0.9) {
                        $this->command->warn("⚠️  Memory usage high: {$memoryUsage}MB");
                    }
                }
                
                // Brief pause every 20 batches to prevent database overload
                if ($batchCount % 20 === 0) {
                    usleep(100000); // 0.1 second pause
                    $this->command->info("⏸️  Brief pause for database recovery");
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Batch {$batchCount} failed: " . $e->getMessage());
                Log::error("MassBookSeeder batch failure", [
                    'batch' => $batchCount,
                    'inserted' => $inserted,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Continue with next batch instead of stopping completely
                continue;
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        $finalRate = self::TOTAL_RECORDS / $totalTime;
        $finalMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        // Final statistics
        $this->command->newLine();
        $this->command->info('🎉 Mass Book Seeding Complete!');
        $this->command->info('================================');
        $this->command->info("📊 Total Records: " . number_format($inserted));
        $this->command->info("⏱️  Total Time: " . number_format($totalTime, 2) . " seconds");
        $this->command->info("🚀 Average Rate: " . number_format($finalRate, 0) . " records/second");
        $this->command->info("💾 Peak Memory: " . number_format($finalMemory, 2) . " MB");
        $this->command->info("📦 Total Batches: {$batchCount}");
        
        // Performance validation
        $this->validatePerformance($totalTime, $finalMemory);
        
        // Data integrity checks
        $this->validateDataIntegrity();
        
        // Re-enable query logging
        DB::enableQueryLog();
    }
    
    /**
     * Validate performance against Lab 7 requirements
     */
    private function validatePerformance(float $totalTime, float $peakMemory): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Performance Validation:');
        $this->command->info('------------------------');
        
        // Time requirement: less than 10 minutes (600 seconds)
        $timeRequirement = 600; // seconds
        if ($totalTime <= $timeRequirement) {
            $this->command->info("✅ Time requirement met: " . number_format($totalTime, 2) . "s < {$timeRequirement}s");
        } else {
            $this->command->error("❌ Time requirement exceeded: " . number_format($totalTime, 2) . "s > {$timeRequirement}s");
        }
        
        // Memory requirement: less than 512 MB
        $memoryRequirement = self::MEMORY_LIMIT_MB;
        if ($peakMemory <= $memoryRequirement) {
            $this->command->info("✅ Memory requirement met: " . number_format($peakMemory, 2) . "MB < {$memoryRequirement}MB");
        } else {
            $this->command->error("❌ Memory requirement exceeded: " . number_format($peakMemory, 2) . "MB > {$memoryRequirement}MB");
        }
    }
    
    /**
     * Validate data integrity
     */
    private function validateDataIntegrity(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Data Integrity Validation:');
        $this->command->info('---------------------------');
        
        // Check total count
        $actualCount = DB::table('books')->count();
        $this->command->info("📊 Total books in database: " . number_format($actualCount));
        
        if ($actualCount === self::TOTAL_RECORDS) {
            $this->command->info("✅ Record count matches target");
        } else {
            $this->command->error("❌ Record count mismatch: expected " . self::TOTAL_RECORDS . ", got {$actualCount}");
        }
        
        // Check ISBN format validity
        $invalidIsbns = DB::table('books')
            ->whereRaw('LENGTH(isbn) != 13')
            ->orWhereRaw('isbn NOT REGEXP "^[0-9]{13}$"')
            ->count();
            
        if ($invalidIsbns === 0) {
            $this->command->info("✅ All ISBNs are valid 13-digit numbers");
        } else {
            $this->command->error("❌ Found {$invalidIsbns} invalid ISBNs");
        }
        
        // Check foreign key relationships
        $nullCategories = DB::table('books')->whereNull('category_id')->count();
        if ($nullCategories === 0) {
            $this->command->info("✅ All books have valid category relationships");
        } else {
            $this->command->error("❌ Found {$nullCategories} books with null category_id");
        }
        
        // Check data distribution
        $activeBooks = DB::table('books')->where('is_active', true)->count();
        $activePercent = ($activeBooks / $actualCount) * 100;
        $this->command->info("📈 Active books: " . number_format($activeBooks) . " (" . number_format($activePercent, 1) . "%)");
        
        // Check price distribution
        $priceStats = DB::table('books')->selectRaw('
            COUNT(*) as total,
            AVG(price) as avg_price,
            MIN(price) as min_price,
            MAX(price) as max_price
        ')->first();
        
        $this->command->info("💰 Price range: $" . $priceStats->min_price . " - $" . $priceStats->max_price);
        $this->command->info("💰 Average price: $" . $priceStats->avg_price);
        
        // Check format distribution
        $formatStats = DB::table('books')
            ->select('format', DB::raw('COUNT(*) as count'))
            ->groupBy('format')
            ->orderBy('count', 'desc')
            ->get();
            
        $this->command->info("📚 Format distribution:");
        foreach ($formatStats as $stat) {
            $percent = ($stat->count / $actualCount) * 100;
            $this->command->info("   " . $stat->format . ": " . number_format($stat->count) . " (" . number_format($percent, 1) . "%)");
        }
    }
}
