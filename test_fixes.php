<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Testing Lab 7 Fixes\n";
echo "=====================\n\n";

// Test 1: Lab 7 fields
echo "📋 Test 1: Lab 7 Fields\n";
$fields = ['publisher', 'format', 'is_active', 'published_at', 'pages', 'language', 'dimensions', 'weight'];
$allFieldsExist = true;

foreach ($fields as $field) {
    $exists = \Illuminate\Support\Facades\Schema::hasColumn('books', $field);
    $status = $exists ? "✅" : "❌";
    echo "  $field: $status\n";
    if (!$exists) $allFieldsExist = false;
}

echo "\n";

// Test 2: Performance indexes
echo "📋 Test 2: Performance Indexes\n";
$indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM books WHERE Key_name LIKE 'idx_books_%'");
echo "  Found " . count($indexes) . " performance indexes\n";
foreach ($indexes as $index) {
    echo "  ✅ {$index->Key_name}\n";
}

echo "\n";

// Test 3: Data status
echo "📋 Test 3: Data Status\n";
$totalBooks = \Illuminate\Support\Facades\DB::table('books')->count();
$activeBooks = \Illuminate\Support\Facades\DB::table('books')->where('is_active', true)->count();
$validIsbns = \Illuminate\Support\Facades\DB::table('books')->whereRaw('LENGTH(isbn) = 13')->whereRaw('isbn REGEXP \"^[0-9]{13}$\"')->count();

echo "  Total books: $totalBooks\n";
echo "  Active books: $activeBooks\n";
echo "  Valid ISBNs: $validIsbns\n";

echo "\n";

// Test 4: Debug packages
echo "📋 Test 4: Debug Packages\n";
$debugbarExists = class_exists('Barryvdh\Debugbar\LaravelDebugbar');
$queryDetectorExists = class_exists('BeyondCode\QueryDetector\QueryDetectorMiddleware');

echo "  DebugBar: " . ($debugbarExists ? "✅ INSTALLED" : "❌ MISSING") . "\n";
echo "  Query Detector: " . ($queryDetectorExists ? "✅ INSTALLED" : "❌ MISSING") . "\n";

echo "\n";

// Test 5: Repository and Services
echo "📋 Test 5: Classes\n";
$classes = [
    'BookRepository' => 'App\Repositories\BookRepository',
    'BookCacheService' => 'App\Services\BookCacheService',
    'OptimizedBookController' => 'App\Http\Controllers\Api\OptimizedBookController',
];

foreach ($classes as $name => $class) {
    $exists = class_exists($class);
    echo "  $name: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

echo "\n";

// Summary
echo "📊 Summary\n";
echo "=========\n";
echo "Lab 7 Fields: " . ($allFieldsExist ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Performance Indexes: " . (count($indexes) >= 5 ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Data Integrity: " . ($validIsbns == $totalBooks ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Debug Tools: " . ($debugbarExists && $queryDetectorExists ? "✅ PASS" : "❌ FAIL") . "\n";

echo "\n🎉 Testing Complete!\n";
