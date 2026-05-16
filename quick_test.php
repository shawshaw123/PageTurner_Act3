<?php

/**
 * Quick Lab 7 Testing Script
 * Run: php quick_test.php
 */

echo "🚀 Lab 7 Quick Testing Script\n";
echo "==============================\n\n";

// Step 1: System Verification
echo "📋 Step 1: System Verification\n";
echo "------------------------------\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check basic components
$checks = [
    'Books table exists' => function() { return \Illuminate\Support\Facades\Schema::hasTable('books'); },
    'Lab 7 fields present' => function() { 
        return \Illuminate\Support\Facades\Schema::hasColumn('books', 'publisher') && 
               \Illuminate\Support\Facades\Schema::hasColumn('books', 'format') &&
               \Illuminate\Support\Facades\Schema::hasColumn('books', 'is_active'); 
    },
    'Performance indexes' => function() { 
        $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM books WHERE Key_name LIKE 'idx_books_%'");
        return count($indexes) >= 5; 
    },
    'BookRepository exists' => function() { return class_exists('App\Repositories\BookRepository'); },
    'BookCacheService exists' => function() { return class_exists('App\Services\BookCacheService'); },
    'OptimizedBookController exists' => function() { return class_exists('App\Http\Controllers\Api\OptimizedBookController'); },
];

foreach ($checks as $name => $check) {
    $result = $check() ? "✅ PASS" : "❌ FAIL";
    echo sprintf("%-30s %s\n", $name, $result);
}

echo "\n";

// Step 2: Data Status
echo "📊 Step 2: Data Status\n";
echo "---------------------\n";

$currentBooks = \Illuminate\Support\Facades\DB::table('books')->count();
$activeBooks = \Illuminate\Support\Facades\DB::table('books')->where('is_active', true)->count();
$categories = \Illuminate\Support\Facades\DB::table('categories')->count();

echo sprintf("%-25s %s\n", "Total Books:", number_format($currentBooks));
echo sprintf("%-25s %s\n", "Active Books:", number_format($activeBooks));
echo sprintf("%-25s %s\n", "Categories:", number_format($categories));

if ($currentBooks > 0) {
    $avgPrice = \Illuminate\Support\Facades\DB::table('books')->avg('price');
    $invalidIsbns = \Illuminate\Support\Facades\DB::table('books')
        ->whereRaw('LENGTH(isbn) != 13')
        ->orWhereRaw('isbn NOT REGEXP \"^[0-9]{13}$\"')
        ->count();
    
    echo sprintf("%-25s \$%.2f\n", "Average Price:", $avgPrice);
    echo sprintf("%-25s %s\n", "Invalid ISBNs:", $invalidIsbns . " (should be 0)");
}

echo "\n";

// Step 3: Performance Test (if server is running)
echo "⚡ Step 3: Performance Test\n";
echo "---------------------------\n";

$serverRunning = false;
$testUrl = 'http://127.0.0.1:8000/api/books';

// Test if server is running
$context = stream_context_create([
    'http' => [
        'timeout' => 2,
        'method' => 'GET'
    ]
]);

$startTime = microtime(true);
try {
    $response = @file_get_contents($testUrl, false, $context);
    $endTime = microtime(true);
    
    if ($response !== false) {
        $serverRunning = true;
        $responseTime = ($endTime - $startTime) * 1000;
        echo sprintf("%-25s %s\n", "Server Status:", "✅ Running");
        echo sprintf("%-25s %s\n", "API Response:", "✅ Working");
        echo sprintf("%-25s %.2f ms\n", "Response Time:", $responseTime);
        
        if ($responseTime < 100) {
            echo sprintf("%-25s %s\n", "Performance:", "✅ Target Met (< 100ms)");
        } else {
            echo sprintf("%-25s %s\n", "Performance:", "❌ Target Not Met (> 100ms)");
        }
    }
} catch (Exception $e) {
    echo sprintf("%-25s %s\n", "Server Status:", "❌ Not Running");
    echo sprintf("%-25s %s\n", "API Response:", "❌ Not Tested");
}

if (!$serverRunning) {
    echo "\n💡 To test performance, start the server:\n";
    echo "   php artisan serve\n";
    echo "   Then run this script again.\n";
}

echo "\n";

// Step 4: Recommendations
echo "🎯 Step 4: Next Steps\n";
echo "--------------------\n";

if ($currentBooks < 1000) {
    echo "📦 Recommended: Test mass seeding\n";
    echo "   Command: php artisan db:seed --class=MassBookSeeder\n";
    echo "   ⚠️  This will generate 1M records and take several minutes\n\n";
}

if (!$serverRunning) {
    echo "🚀 Recommended: Start the server\n";
    echo "   Command: php artisan serve\n\n";
}

if ($serverRunning && $currentBooks > 0) {
    echo "🧪 Recommended: Test API endpoints\n";
    echo "   Catalog: curl \"$testUrl\"\n";
    echo "   Bestsellers: curl \"http://127.0.0.1:8000/api/books/bestsellers\"\n";
    echo "   Cache Warm: curl -X POST \"http://127.0.0.1:8000/api/books/cache/warm\"\n\n";
}

echo "📚 For detailed testing, see: LAB7_TESTING_PLAN.md\n";
echo "🔍 For implementation details, see: LAB7_IMPLEMENTATION_GUIDE.md\n";

echo "\n🎉 Lab 7 Quick Test Complete!\n";
