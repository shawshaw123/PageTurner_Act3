# 🧪 Laboratory Activity 7 - Complete Testing Plan

## 🎯 **How to Test All Lab 7 Features**

This guide provides step-by-step instructions to test every implemented feature.

---

## 📋 **Pre-Testing Checklist**

Before you start, make sure:
- ✅ Laravel application is running (`php artisan serve`)
- ✅ Database migrations are up to date
- ✅ Redis server is running (for caching tests)
- ✅ Queue worker is running (`php artisan queue:work`)

---

## 🚀 **Phase 1: System Verification**

### **1.1 Quick System Check**
```bash
# Run the verification script
php lab7_test.php
```

**Expected Output**: All tests should show ✅ PASS

### **1.2 Database Schema Verification**
```bash
php artisan tinker --execute "
echo '🔍 Database Schema Check:' . PHP_EOL;
echo '✅ Books table: ' . (Schema::hasTable('books') ? 'EXISTS' : 'MISSING') . PHP_EOL;
echo '✅ Lab 7 fields: ' . (Schema::hasColumn('books', 'publisher') ? 'EXISTS' : 'MISSING') . PHP_EOL;
echo '✅ Performance indexes: ' . count(DB::select('SHOW INDEX FROM books WHERE Key_name LIKE \"idx_books_%\"')) . ' indexes' . PHP_EOL;
echo '✅ Current books: ' . DB::table('books')->count() . ' records' . PHP_EOL;
"
```

---

## 📦 **Phase 2: Mass Data Seeding Test**

### **2.1 Test Small Batch First**
```bash
# Test with 1000 records first
php artisan tinker --execute "
\$factory = App\Models\Book::factory();
\$books = \$factory->count(1000)->make();
echo '✅ Generated ' . count(\$books) . ' books in memory' . PHP_EOL;
echo '✅ Memory usage: ' . (memory_get_usage(true) / 1024 / 1024) . ' MB' . PHP_EOL;
"
```

### **2.2 Full Mass Seeding (1M Records)**
⚠️ **WARNING**: This will take 5-10 minutes and use significant memory

```bash
# Run the MassBookSeeder
php artisan db:seed --class=MassBookSeeder

# Monitor progress - you'll see real-time updates
```

**Expected Results**:
- ✅ Total records: 1,000,000
- ✅ Memory usage: < 512 MB
- ✅ Time taken: < 10 minutes
- ✅ All ISBNs valid (13 digits)
- ✅ All foreign keys valid

### **2.3 Verify Seeding Results**
```bash
php artisan tinker --execute "
echo '📊 Seeding Results:' . PHP_EOL;
echo 'Total Books: ' . number_format(DB::table('books')->count()) . PHP_EOL;
echo 'Active Books: ' . number_format(DB::table('books')->where('is_active', true)->count()) . PHP_EOL;
echo 'Categories Used: ' . DB::table('books')->distinct('category_id')->count('category_id') . PHP_EOL;
echo 'Publishers Used: ' . DB::table('books')->distinct('publisher')->count('publisher') . PHP_EOL;
echo 'Avg Price: \$' . DB::table('books')->avg('price') . PHP_EOL;

# Check ISBN validation
\$invalidIsbns = DB::table('books')
    ->whereRaw('LENGTH(isbn) != 13')
    ->orWhereRaw('isbn NOT REGEXP \"^[0-9]{13}$\"')
    ->count();
echo 'Invalid ISBNs: ' . \$invalidIsbns . ' (should be 0)' . PHP_EOL;
"
```

---

## ⚡ **Phase 3: Performance Testing**

### **3.1 Test Catalog Performance**
```bash
# Start the server if not running
php artisan serve

# Test 1: Catalog listing (target: < 100ms)
time curl -s "http://127.0.0.1:8000/api/books?per_page=100" -H "Accept: application/json" > /dev/null

# Test 2: Catalog with filters (target: < 150ms)
time curl -s "http://127.0.0.1:8000/api/books?category_id=1&min_price=10&max_price=50" -H "Accept: application/json" > /dev/null

# Test 3: Large page size (target: < 200ms)
time curl -s "http://127.0.0.1:8000/api/books?per_page=1000" -H "Accept: application/json" > /dev/null
```

### **3.2 Test ISBN Lookup Performance**
```bash
# Get a sample ISBN first
ISBN=$(php artisan tinker --execute "echo DB::table('books')->first()->isbn;" 2>/dev/null | tail -1)

# Test ISBN lookup (target: < 50ms)
time curl -s "http://127.0.0.1:8000/api/books/isbn/$ISBN" -H "Accept: application/json" > /dev/null
```

### **3.3 Test Search Performance**
```bash
# Test search (target: < 300ms)
time curl -s "http://127.0.0.1:8000/api/books/search?q=fiction&per_page=100" -H "Accept: application/json" > /dev/null

# Test search with filters
time curl -s "http://127.0.0.1:8000/api/books/search?q=mystery&category_id=1" -H "Accept: application/json" > /dev/null
```

---

## 💾 **Phase 4: Caching Tests**

### **4.1 Test Cache Warming**
```bash
# Warm up the cache
curl -X POST "http://127.0.0.1:8000/api/books/cache/warm" -H "Accept: application/json"

# Expected: {"message":"Cache warmed successfully","timestamp":"..."}
```

### **4.2 Test Cache Performance**
```bash
# First request (cache miss)
time curl -s "http://127.0.0.1:8000/api/books?per_page=100" -H "Accept: application/json" > /dev/null

# Second request (cache hit - should be much faster)
time curl -s "http://127.0.0.1:8000/api/books?per_page=100" -H "Accept: application/json" > /dev/null

# Expected: Second request should be < 10ms
```

### **4.3 Test Cache Statistics**
```bash
# Get cache health and statistics
curl "http://127.0.0.1:8000/api/books/cache/stats" -H "Accept: application/json"

# Expected: Detailed cache statistics including memory usage, hit rate, etc.
```

### **4.4 Test Cache Invalidation**
```bash
# Clear specific cache
curl -X DELETE "http://127.0.0.1:8000/api/books/cache?category_id=1" -H "Accept: application/json"

# Expected: {"message":"Cache cleared successfully",...}
```

---

## 🗄️ **Phase 5: Database Optimization Tests**

### **5.1 Test Index Usage**
```bash
php artisan tinker --execute "
// Enable query log
DB::enableQueryLog();

// Test catalog query (should use covering index)
\$books = DB::table('books')
    ->select(['id', 'title', 'author', 'price', 'category_id', 'is_active'])
    ->where('is_active', true)
    ->orderBy('published_at', 'desc')
    ->limit(100)
    ->get();

\$queries = DB::getQueryLog();
foreach (\$queries as \$query) {
    echo 'Query: ' . \$query['query'] . PHP_EOL;
    echo 'Time: ' . \$query['time'] . 'ms' . PHP_EOL;
}

DB::disableQueryLog();
"
```

### **5.2 Test Partitioning (if supported)**
```bash
php artisan tinker --execute "
// Check if partitioning is active
try {
    \$partitions = DB::select('
        SELECT PARTITION_NAME, TABLE_ROWS 
        FROM INFORMATION_SCHEMA.PARTITIONS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = \"books\" 
        AND PARTITION_NAME IS NOT NULL
    ');
    
    echo 'Partitions found: ' . count(\$partitions) . PHP_EOL;
    foreach (\$partitions as \$partition) {
        echo '  ' . \$partition->PARTITION_NAME . ': ' . \$partition->TABLE_ROWS . ' rows' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Partitioning not available: ' . \$e->getMessage() . PHP_EOL;
}
"
```

---

## 🔄 **Phase 6: Advanced Features Tests**

### **6.1 Test Bestsellers**
```bash
# Get bestsellers (should be cached)
curl "http://127.0.0.1:8000/api/books/bestsellers" -H "Accept: application/json"

# Expected: Array of bestseller books with cached=true
```

### **6.2 Test New Releases**
```bash
# Get new releases
curl "http://127.0.0.1:8000/api/books/new-releases" -H "Accept: application/json"

# Expected: Array of recent publications
```

### **6.3 Test Similar Books**
```bash
# Get similar books (need a valid book ID)
BOOK_ID=$(php artisan tinker --execute "echo DB::table('books')->first()->id;" 2>/dev/null | tail -1)

curl "http://127.0.0.1:8000/api/books/$BOOK_ID/similar" -H "Accept: application/json"

# Expected: Array of similar books based on category and price
```

### **6.4 Test Price Statistics**
```bash
# Get price statistics for a category
curl "http://127.0.0.1:8000/api/books/price-stats/1" -H "Accept: application/json"

# Expected: Price statistics including min, max, average, median
```

---

## 📊 **Phase 7: Load Testing**

### **7.1 Concurrent Request Test**
```bash
# Install ab (Apache Bench) if not available
# On Ubuntu/Debian: sudo apt-get install apache2-utils
# On Windows: Use WSL or alternative tool

# Test 50 concurrent requests
ab -n 100 -c 10 "http://127.0.0.1:8000/api/books" -H "Accept: application/json"

# Expected: All requests succeed with < 200ms average response time
```

### **7.2 Stress Test Catalog Endpoint**
```bash
# Test with different parameters
ab -n 200 -c 20 "http://127.0.0.1:8000/api/books?per_page=50&category_id=1" -H "Accept: application/json"

# Expected: Consistent performance under load
```

---

## 🔍 **Phase 8: Debugging and Monitoring**

### **8.1 Enable DebugBar**
```bash
# Check if DebugBar is working
curl "http://127.0.0.1:8000/api/books" -H "Accept: application/json" 2>/dev/null | jq .debugBar > /dev/null

# Or visit in browser: http://127.0.0.1:8000/api/books
# Look for DebugBar at bottom of page
```

### **8.2 Monitor Query Performance**
```bash
# Enable query detector in .env
QUERY_DETECTOR_ENABLED=true

# Make some requests and check logs
tail -f storage/logs/laravel.log

# You should see detected N+1 queries (if any)
```

---

## 📈 **Phase 9: Performance Benchmarking**

### **9.1 Create Benchmark Script**
```bash
cat > benchmark.php << 'EOF'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$iterations = 100;
$endpoint = 'http://127.0.0.1:8000/api/books';

echo "🏁 Benchmarking $iterations requests to $endpoint\n";

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    file_get_contents($endpoint);
}
$end = microtime(true);

$totalTime = $end - $start;
$avgTime = ($totalTime / $iterations) * 1000;

echo "Total time: " . number_format($totalTime, 2) . "s\n";
echo "Average time: " . number_format($avgTime, 2) . "ms\n";
echo "Requests per second: " . number_format($iterations / $totalTime, 0) . "\n";

if ($avgTime < 100) {
    echo "✅ PERFORMANCE TARGET MET (< 100ms)\n";
} else {
    echo "❌ PERFORMANCE TARGET NOT MET (> 100ms)\n";
}
EOF

php benchmark.php
```

---

## 🎯 **Phase 10: Validation Checklist**

### **10.1 Performance Validation**
```bash
echo "📊 Performance Validation Checklist"
echo "=================================="

# Test each performance target
echo "Testing catalog performance (< 100ms)..."
time curl -s "http://127.0.0.1:8000/api/books?per_page=100" > /dev/null

echo "Testing ISBN lookup (< 50ms)..."
ISBN=$(php artisan tinker --execute "echo DB::table('books')->first()->isbn;" 2>/dev/null | tail -1)
time curl -s "http://127.0.0.1:8000/api/books/isbn/$ISBN" > /dev/null

echo "Testing category filter (< 150ms)..."
time curl -s "http://127.0.0.1:8000/api/books?category_id=1" > /dev/null

echo "Testing search (< 300ms)..."
time curl -s "http://127.0.0.1:8000/api/books/search?q=fiction" > /dev/null
```

### **10.2 Data Integrity Validation**
```bash
php artisan tinker --execute "
echo '🔍 Data Integrity Validation' . PHP_EOL;
echo '=============================' . PHP_EOL;

// Check total records
\$total = DB::table('books')->count();
echo 'Total books: ' . number_format(\$total) . PHP_EOL;

// Check ISBN format
\$invalidIsbns = DB::table('books')
    ->whereRaw('LENGTH(isbn) != 13')
    ->orWhereRaw('isbn NOT REGEXP \"^[0-9]{13}$\"')
    ->count();
echo 'Invalid ISBNs: ' . \$invalidIsbns . ' (should be 0)' . PHP_EOL;

// Check foreign keys
\$nullCategories = DB::table('books')->whereNull('category_id')->count();
echo 'Null categories: ' . \$nullCategories . ' (should be 0)' . PHP_EOL;

// Check data distribution
\$activeBooks = DB::table('books')->where('is_active', true)->count();
\$activePercent = (\$activeBooks / \$total) * 100;
echo 'Active books: ' . number_format(\$activeBooks) . ' (' . number_format(\$activePercent, 1) . '%)' . PHP_EOL;

echo '✅ Data integrity check complete!' . PHP_EOL;
"
```

---

## 🎉 **Success Criteria**

### **All Tests Pass When:**
- ✅ System verification shows all components installed
- ✅ Mass seeding completes within 10 minutes and 512MB memory
- ✅ All performance targets met (< 100ms catalog, < 50ms ISBN lookup)
- ✅ Caching works (second requests < 10ms)
- ✅ Data integrity validated (no invalid ISBNs or null FKs)
- ✅ Load testing handles concurrent requests
- ✅ Debug tools show no N+1 queries

---

## 🆘 **Troubleshooting**

### **Common Issues and Solutions:**

#### **Mass Seeding Fails**
```bash
# Check memory limit
php -i | grep memory_limit

# Increase memory limit in php.ini
memory_limit = 1024M

# Or run in smaller batches
php artisan tinker --execute "
for (\$i = 0; \$i < 10; \$i++) {
    \$books = App\Models\Book::factory()->count(100000)->make();
    DB::table('books')->insert(\$books->toArray());
    echo 'Batch ' . (\$i + 1) . ' complete' . PHP_EOL;
    unset(\$books);
    gc_collect_cycles();
}
"
```

#### **Performance Targets Not Met**
```bash
# Check indexes
php artisan tinker --execute "
\$indexes = DB::select('SHOW INDEX FROM books WHERE Key_name LIKE \"idx_books_%\"');
foreach (\$indexes as \$index) {
    echo \$index->Key_name . PHP_EOL;
}
"

# Check slow queries
php artisan tinker --execute "
DB::enableQueryLog();
// Run your slow query here
\$queries = DB::getQueryLog();
foreach (\$queries as \$query) {
    if (\$query['time'] > 100) {
        echo 'Slow query: ' . \$query['time'] . 'ms' . PHP_EOL;
    }
}
"
```

#### **Cache Not Working**
```bash
# Check Redis connection
php artisan tinker --execute "
try {
    Redis::connection()->ping();
    echo '✅ Redis connected' . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
}
"

# Check cache configuration
php artisan tinker --execute "
echo 'Cache driver: ' . config('cache.default') . PHP_EOL;
echo 'Redis driver: ' . config('cache.stores.redis.driver') . PHP_EOL;
"
```

---

## 📈 **Expected Results Summary**

When all tests pass, you should have:
- 🚀 **1,000,000 books** in database with valid data
- ⚡ **Sub-100ms response times** for catalog queries
- 💾 **Working Redis cache** with < 10ms cached responses
- 🗄️ **Optimized database** with strategic indexes
- 🔍 **No N+1 queries** detected by debug tools
- 📊 **Performance monitoring** working correctly

**🎊 Your PageTurner Bookstore is enterprise-ready!**
