<?php

namespace Tests\Performance;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookCatalogLoadTest extends TestCase
{
    /**
     * Test concurrent catalog requests
     * Simulates 50 concurrent requests
     */
    public function test_concurrent_catalog_requests(): void
    {
        $startTime = microtime(true);
        $responses = [];
        
        // In a real environment, we would use Guzzle or parallel processing
        // For standard PHPUnit, we simulate sequential but rapid requests
        for ($i = 0; $i < 50; $i++) {
            $response = $this->getJson('/api/books?per_page=100');
            $responses[] = $response;
            
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data', 'links', 'meta'
            ]);
        }
        
        $duration = microtime(true) - $startTime;
        $avgTime = ($duration / 50) * 1000; // in ms
        
        echo "\n🚀 Load Test: 50 Catalog Requests\n";
        echo "   Total Time: " . number_format($duration, 2) . "s\n";
        echo "   Average Time: " . number_format($avgTime, 2) . "ms\n";
        
        $this->assertLessThan(100, $avgTime, 'Average catalog response time should be less than 100ms');
    }

    /**
     * Test ISBN lookup performance
     */
    public function test_isbn_lookup_performance(): void
    {
        $book = Book::first();
        if (!$book) {
            $this->markTestSkipped('No books found to test ISBN lookup');
        }
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $response = $this->getJson("/api/books/isbn/{$book->isbn}");
            $response->assertStatus(200);
        }
        
        $duration = microtime(true) - $startTime;
        $avgTime = ($duration / 100) * 1000; // in ms
        
        echo "\n📚 Load Test: 100 ISBN Lookups\n";
        echo "   Average Time: " . number_format($avgTime, 2) . "ms\n";
        
        $this->assertLessThan(50, $avgTime, 'Average ISBN lookup time should be less than 50ms');
    }

    /**
     * Test cache hit efficiency
     */
    public function test_cache_hit_efficiency(): void
    {
        $book = Book::first();
        
        // First request to prime cache
        $this->getJson("/api/books/isbn/{$book->isbn}");
        
        $startTime = microtime(true);
        
        // 50 subsequent requests should be significantly faster
        for ($i = 0; $i < 50; $i++) {
            $this->getJson("/api/books/isbn/{$book->isbn}");
        }
        
        $duration = microtime(true) - $startTime;
        $avgTime = ($duration / 50) * 1000; // in ms
        
        echo "\n💾 Load Test: 50 Cache Hits\n";
        echo "   Average Time: " . number_format($avgTime, 2) . "ms\n";
        
        $this->assertLessThan(10, $avgTime, 'Cache hits should be faster than 10ms');
    }
}
