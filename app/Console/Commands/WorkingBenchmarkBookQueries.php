<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkingBenchmarkBookQueries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:working-book-queries 
                            {--iterations=50 : Number of test iterations}
                            {--output=console : Output format (console|json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Working benchmark for book query performance (uses existing columns)';

    protected array $results = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = $this->option('iterations');
        $output = $this->option('output');

        $this->info('🏁 Lab 7 Working Query Performance Benchmark');
        $this->info('==========================================');
        $this->info("Iterations: $iterations");
        $this->info("Output: $output");
        $this->newLine();

        // Check available columns first
        $this->checkAvailableColumns();

        // Enable query logging for analysis
        DB::enableQueryLog();

        // Run benchmarks
        $this->benchmarkIsbnLookup($iterations);
        $this->benchmarkCatalogListing($iterations);
        $this->benchmarkCategoryFilter($iterations);
        $this->benchmarkTextSearch(min(30, $iterations));

        DB::disableQueryLog();

        // Output results
        $this->outputResults($output);

        // Validate against targets
        $this->validateTargets();

        return Command::SUCCESS;
    }

    /**
     * Check what columns are available
     */
    private function checkAvailableColumns(): void
    {
        $columns = Schema::getColumnListing('books');
        $this->info('📊 Available columns in books table:');
        foreach ($columns as $column) {
            $this->line("   - {$column}");
        }
        $this->newLine();
    }

    /**
     * Benchmark ISBN lookup (target: < 50ms)
     */
    private function benchmarkIsbnLookup(int $iterations): void
    {
        $this->info('📚 Benchmarking ISBN Lookup (Target: < 50ms)');
        
        // Get a sample ISBN
        $isbn = Book::first()->isbn;
        $times = [];
        
        // Benchmark
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $book = Book::with(['category:id,name'])
                ->where('isbn', $isbn)
                ->first();
            $times[] = (microtime(true) - $start) * 1000;
        }
        
        $this->results['isbn_lookup'] = [
            'target' => 50,
            'iterations' => $iterations,
            'times' => $times,
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
            'p95' => $this->percentile($times, 95),
        ];
        
        $this->line("   Average: " . number_format($this->results['isbn_lookup']['avg'], 2) . "ms");
        $this->line("   P95: " . number_format($this->results['isbn_lookup']['p95'], 2) . "ms");
        $this->newLine();
    }

    /**
     * Benchmark catalog listing (target: < 100ms)
     */
    private function benchmarkCatalogListing(int $iterations): void
    {
        $this->info('📖 Benchmarking Catalog Listing (Target: < 100ms)');
        
        $times = [];
        
        // Benchmark
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $books = Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
                ->with(['category:id,name'])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get();
            $times[] = (microtime(true) - $start) * 1000;
        }
        
        $this->results['catalog_listing'] = [
            'target' => 100,
            'iterations' => $iterations,
            'times' => $times,
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
            'p95' => $this->percentile($times, 95),
        ];
        
        $this->line("   Average: " . number_format($this->results['catalog_listing']['avg'], 2) . "ms");
        $this->line("   P95: " . number_format($this->results['catalog_listing']['p95'], 2) . "ms");
        $this->newLine();
    }

    /**
     * Benchmark category filter (target: < 150ms)
     */
    private function benchmarkCategoryFilter(int $iterations): void
    {
        $this->info('🏷️  Benchmarking Category Filter (Target: < 150ms)');
        
        $categoryId = Book::first()->category_id;
        $times = [];
        
        // Benchmark
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $categoryBooks = Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
                ->with(['category:id,name'])
                ->where('category_id', $categoryId)
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get();
            $times[] = (microtime(true) - $start) * 1000;
        }
        
        $this->results['category_filter'] = [
            'target' => 150,
            'iterations' => $iterations,
            'times' => $times,
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
            'p95' => $this->percentile($times, 95),
        ];
        
        $this->line("   Average: " . number_format($this->results['category_filter']['avg'], 2) . "ms");
        $this->line("   P95: " . number_format($this->results['category_filter']['p95'], 2) . "ms");
        $this->newLine();
    }

    /**
     * Benchmark text search (target: < 300ms)
     */
    private function benchmarkTextSearch(int $iterations): void
    {
        $this->info('🔍 Benchmarking Text Search (Target: < 300ms)');
        
        $times = [];
        
        // Benchmark
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $searchResults = Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
                ->with(['category:id,name'])
                ->where('title', 'like', '%Book%')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
            $times[] = (microtime(true) - $start) * 1000;
        }
        
        $this->results['text_search'] = [
            'target' => 300,
            'iterations' => $iterations,
            'times' => $times,
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
            'p95' => $this->percentile($times, 95),
        ];
        
        $this->line("   Average: " . number_format($this->results['text_search']['avg'], 2) . "ms");
        $this->line("   P95: " . number_format($this->results['text_search']['p95'], 2) . "ms");
        $this->newLine();
    }

    /**
     * Calculate percentile
     */
    private function percentile(array $times, float $percentile): float
    {
        sort($times);
        $index = ($percentile / 100) * (count($times) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower === $upper) {
            return $times[$lower];
        }
        
        $weight = $index - $lower;
        return $times[$lower] * (1 - $weight) + $times[$upper] * $weight;
    }

    /**
     * Output results in specified format
     */
    private function outputResults(string $format): void
    {
        switch ($format) {
            case 'json':
                $this->line(json_encode($this->results, JSON_PRETTY_PRINT));
                break;
                
            case 'csv':
                $this->line('Test,Target,Iterations,Avg,Min,Max,P95,Status');
                foreach ($this->results as $test => $data) {
                    $status = $data['avg'] <= $data['target'] ? 'PASS' : 'FAIL';
                    $this->line("{$test},{$data['target']},{$data['iterations']},{$data['avg']},{$data['min']},{$data['max']},{$data['p95']},{$status}");
                }
                break;
                
            default: // console
                $this->info('📊 Benchmark Results Summary:');
                $this->info('===========================');
                
                foreach ($this->results as $test => $data) {
                    $status = $data['avg'] <= $data['target'] ? '✅ PASS' : '❌ FAIL';
                    $target = number_format($data['target'], 0);
                    $avg = number_format($data['avg'], 2);
                    $p95 = number_format($data['p95'], 2);
                    
                    $this->line(sprintf("%-20s Target: %sms | Avg: %sms | P95: %sms | %s", 
                        $test, $target, $avg, $p95, $status));
                }
                break;
        }
    }

    /**
     * Validate results against Lab 7 targets
     */
    private function validateTargets(): void
    {
        $this->newLine();
        $this->info('🎯 Lab 7 Target Validation:');
        $this->info('============================');
        
        $allPassed = true;
        $targets = [
            'isbn_lookup' => 'ISBN Lookup (< 50ms)',
            'catalog_listing' => 'Catalog Listing (< 100ms)',
            'category_filter' => 'Category Filter (< 150ms)',
            'text_search' => 'Text Search (< 300ms)',
        ];
        
        foreach ($targets as $key => $name) {
            if (isset($this->results[$key])) {
                $passed = $this->results[$key]['avg'] <= $this->results[$key]['target'];
                $status = $passed ? '✅ PASS' : '❌ FAIL';
                $avg = number_format($this->results[$key]['avg'], 2);
                $target = number_format($this->results[$key]['target'], 0);
                
                $this->line(sprintf("%-25s %s (%sms vs %sms target)", $name, $status, $avg, $target));
                
                if (!$passed) {
                    $allPassed = false;
                }
            }
        }
        
        $this->newLine();
        
        if ($allPassed) {
            $this->info('🎉 ALL PERFORMANCE TARGETS MET!');
            $this->info('Lab 7 query performance requirements satisfied.');
        } else {
            $this->error('❌ Some performance targets not met.');
            $this->error('Review results above for optimization opportunities.');
        }
        
        // Check for N+1 queries
        $this->checkN1Queries();
    }

    /**
     * Check for N+1 query problems
     */
    private function checkN1Queries(): void
    {
        $this->newLine();
        $this->info('🔍 N+1 Query Analysis:');
        $this->info('=======================');
        
        $queryLog = DB::getQueryLog();
        $totalQueries = count($queryLog);
        $duplicateQueries = [];
        
        foreach ($queryLog as $query) {
            $queryHash = md5($query['query'] . serialize($query['bindings']));
            $duplicateQueries[$queryHash] = ($duplicateQueries[$queryHash] ?? 0) + 1;
        }
        
        $potentialN1 = array_filter($duplicateQueries, fn($count) => $count > 10);
        
        if (empty($potentialN1)) {
            $this->info('✅ No obvious N+1 query patterns detected');
        } else {
            $this->warn('⚠️  Potential N+1 query patterns found:');
            foreach ($potentialN1 as $hash => $count) {
                $this->line("   Query executed $count times (potential N+1)");
            }
            $this->warn('Review query patterns and consider eager loading optimization');
        }
        
        $this->line("Total queries analyzed: $totalQueries");
    }
}
