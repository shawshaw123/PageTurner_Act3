<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestMassBookSeeder extends Seeder
{
    /**
     * Test with smaller dataset first
     */
    private const TOTAL_RECORDS = 100000; // 100K instead of 1M
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        // Increase memory limit
        ini_set('memory_limit', '512M');
        
        $this->command->info('🧪 Starting Test Mass Book Seeding...');
        $this->command->info("Target: " . number_format(self::TOTAL_RECORDS) . " records (test dataset)");
        
        $startTime = microtime(true);
        
        // Clear existing books
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::table('books')->truncate();
        } catch (\Exception $e) {
            DB::table('books')->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        DB::disableQueryLog();
        
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $this->error('❌ No categories found.');
            return;
        }
        
        $inserted = 0;
        $batchCount = 0;
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            $books = [];
            for ($i = 0; $i < $batchSize; $i++) {
                $books[] = [
                    'isbn' => '978' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT) . mt_rand(0, 9),
                    'title' => 'Test Book ' . ($inserted + $i + 1),
                    'author' => 'Test Author ' . mt_rand(1, 100),
                    'publisher' => 'Test Publisher',
                    'price' => mt_rand(999, 4999) / 100,
                    'stock_quantity' => mt_rand(0, 1000),
                    'category_id' => $categoryIds[mt_rand(0, count($categoryIds) - 1)],
                    'format' => ['Hardcover', 'Paperback', 'Ebook'][mt_rand(0, 2)],
                    'description' => 'Test description for book ' . ($inserted + $i + 1),
                    'cover_image' => null,
                    'is_active' => mt_rand(1, 100) <= 85 ? 1 : 0,
                    'published_at' => now()->subDays(mt_rand(0, 3650)),
                    'pages' => mt_rand(100, 800),
                    'language' => 'English',
                    'dimensions' => '6 x 9',
                    'weight' => mt_rand(50, 200) / 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            DB::table('books')->insert($books);
            unset($books);
            
            $inserted += $batchSize;
            $batchCount++;
            
            $progress = ($inserted / self::TOTAL_RECORDS) * 100;
            $memory = memory_get_usage(true) / 1024 / 1024;
            
            $this->line(sprintf(
                "[%s] Batch %d: %d records (%.1f%%) | Memory: %.1f MB",
                str_repeat('=', (int)($progress / 2)) . str_repeat(' ', 50 - (int)($progress / 2)),
                $batchCount,
                $inserted,
                $progress,
                $memory
            ));
            
            if ($batchCount % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        
        $this->newLine();
        $this->info('🎉 Test Mass Book Seeding Complete!');
        $this->info('==================================');
        $this->info("📊 Total Records: " . number_format($inserted));
        $this->info("⏱️  Total Time: " . number_format($totalTime, 2) . " seconds");
        $this->info("💾 Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB");
        
        DB::enableQueryLog();
    }
}
