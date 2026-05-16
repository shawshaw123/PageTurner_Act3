<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Lab 7 Performance Test\n";
echo "========================\n\n";

// Test 1: ISBN Lookup (target < 50ms)
echo "📚 Testing ISBN Lookup (Target: < 50ms)\n";
$start = microtime(true);
$book = \App\Models\Book::with(['category:id,name,slug'])
    ->where('isbn', '9780000000001')
    ->first();
$isbnTime = (microtime(true) - $start) * 1000;
echo "   Result: " . number_format($isbnTime, 2) . "ms " . ($isbnTime < 50 ? '✅ PASS' : '❌ FAIL') . "\n\n";

// Test 2: Catalog Listing (target < 100ms)
echo "📖 Testing Catalog Listing (Target: < 100ms)\n";
$start = microtime(true);
$books = \App\Models\Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
    ->with(['category:id,name,slug'])
    ->where('is_active', 1)
    ->orderBy('id', 'desc')
    ->limit(100)
    ->get();
$catalogTime = (microtime(true) - $start) * 1000;
echo "   Result: " . number_format($catalogTime, 2) . "ms " . ($catalogTime < 100 ? '✅ PASS' : '❌ FAIL') . "\n\n";

// Test 3: Category Filter (target < 150ms)
echo "🏷️ Testing Category Filter (Target: < 150ms)\n";
$start = microtime(true);
$categoryBooks = \App\Models\Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
    ->with(['category:id,name,slug'])
    ->where('category_id', 1)
    ->where('is_active', 1)
    ->orderBy('id', 'desc')
    ->limit(100)
    ->get();
$categoryTime = (microtime(true) - $start) * 1000;
echo "   Result: " . number_format($categoryTime, 2) . "ms " . ($categoryTime < 150 ? '✅ PASS' : '❌ FAIL') . "\n\n";

// Test 4: Simple Search (target < 300ms)
echo "🔍 Testing Simple Search (Target: < 300ms)\n";
$start = microtime(true);
$searchResults = \App\Models\Book::select(['id', 'isbn', 'title', 'author', 'price', 'category_id'])
    ->with(['category:id,name,slug'])
    ->where('is_active', 1)
    ->where('title', 'like', '%Book%')
    ->orderBy('id', 'desc')
    ->limit(50)
    ->get();
$searchTime = (microtime(true) - $start) * 1000;
echo "   Result: " . number_format($searchTime, 2) . "ms " . ($searchTime < 300 ? '✅ PASS' : '❌ FAIL') . "\n\n";

// Summary
echo "📊 Performance Summary\n";
echo "=====================\n";
echo "ISBN Lookup: " . number_format($isbnTime, 2) . "ms " . ($isbnTime < 50 ? '✅' : '❌') . "\n";
echo "Catalog Listing: " . number_format($catalogTime, 2) . "ms " . ($catalogTime < 100 ? '✅' : '❌') . "\n";
echo "Category Filter: " . number_format($categoryTime, 2) . "ms " . ($categoryTime < 150 ? '✅' : '❌') . "\n";
echo "Simple Search: " . number_format($searchTime, 2) . "ms " . ($searchTime < 300 ? '✅' : '❌') . "\n\n";

$allPassed = $isbnTime < 50 && $catalogTime < 100 && $categoryTime < 150 && $searchTime < 300;

if ($allPassed) {
    echo "🎉 ALL PERFORMANCE TARGETS MET!\n";
    echo "Lab 7 Phase 2 requirements satisfied!\n";
} else {
    echo "❌ Some performance targets not met.\n";
    echo "Review results above for optimization opportunities.\n";
}

echo "\n📈 Database Stats:\n";
echo "Total books: " . number_format(\App\Models\Book::count()) . "\n";
echo "Active books: " . \App\Models\Book::where('is_active', 1)->count() . "\n";
echo "Categories: " . \App\Models\Category::count() . "\n";
