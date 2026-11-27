<?php

/**
 * Performance Benchmark Script
 * Run: php benchmark.php
 */

echo "🏁 Lab 7 Performance Benchmark\n";
echo "==============================\n\n";

$endpoint = 'http://127.0.0.1:8000/api/books';
$iterations = 50;

echo "Testing $iterations requests to catalog endpoint...\n\n";

$start = microtime(true);
$success = 0;
$errors = 0;

for ($i = 0; $i < $iterations; $i++) {
    $startRequest = microtime(true);
    
    try {
        $response = file_get_contents($endpoint, false, stream_context_create([
            'http' => ['timeout' => 5, 'method' => 'GET']
        ]));
        
        if ($response !== false) {
            $success++;
            $requestTime = (microtime(true) - $startRequest) * 1000;
            echo "Request " . ($i + 1) . ": " . number_format($requestTime, 2) . "ms\n";
        } else {
            $errors++;
        }
    } catch (Exception $e) {
        $errors++;
        echo "Request " . ($i + 1) . ": ERROR - " . $e->getMessage() . "\n";
    }
    
    // Small delay between requests
    usleep(100000); // 0.1 second
}

$totalTime = microtime(true) - $start;
$avgTime = ($totalTime / $iterations) * 1000;
$rps = $iterations / $totalTime;

echo "\n📊 Benchmark Results:\n";
echo "===================\n";
echo "Total Requests: $iterations\n";
echo "Successful: $success\n";
echo "Errors: $errors\n";
echo "Total Time: " . number_format($totalTime, 2) . "s\n";
echo "Average Time: " . number_format($avgTime, 2) . "ms\n";
echo "Requests/Second: " . number_format($rps, 0) . "\n\n";

// Performance validation
if ($avgTime < 100) {
    echo "✅ PERFORMANCE TARGET MET (< 100ms average)\n";
} else {
    echo "❌ PERFORMANCE TARGET NOT MET (> 100ms average)\n";
}

if ($errors === 0) {
    echo "✅ ALL REQUESTS SUCCESSFUL\n";
} else {
    echo "❌ $errors REQUESTS FAILED\n";
}

if ($rps > 10) {
    echo "✅ GOOD THROUGHPUT (> 10 RPS)\n";
} else {
    echo "❌ LOW THROUGHPUT (< 10 RPS)\n";
}
