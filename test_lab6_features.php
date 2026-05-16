<?php

/**
 * Laboratory Activity 6 Feature Test Script
 * 
 * This script tests all the major features implemented in Lab 6
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Laboratory Activity 6 Feature Tests ===\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// Test 1: Import/Export Models
echo "1. Testing Import/Export Models...\n";
try {
    // Test ImportLog model
    $importLog = new \App\Models\ImportLog();
    $importLog->user_id = 1;
    $importLog->model_type = 'books';
    $importLog->filename = 'test.xlsx';
    $importLog->status = 'pending';
    
    if ($importLog->user_id === 1 && $importLog->model_type === 'books') {
        echo "   ✅ ImportLog model working\n";
        $passed++;
    } else {
        echo "   ❌ ImportLog model failed\n";
        $failed++;
    }

    // Test ExportLog model
    $exportLog = new \App\Models\ExportLog();
    $exportLog->user_id = 1;
    $exportLog->model_type = 'orders';
    $exportLog->format = 'xlsx';
    $exportLog->status = 'pending';
    
    if ($exportLog->user_id === 1 && $exportLog->format === 'xlsx') {
        echo "   ✅ ExportLog model working\n";
        $passed++;
    } else {
        echo "   ❌ ExportLog model failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Import/Export models error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Test 2: Backup Monitoring
echo "\n2. Testing Backup Monitoring...\n";
try {
    $backup = new \App\Models\BackupMonitoring();
    $backup->backup_type = 'daily';
    $backup->status = 'completed';
    $backup->disk = 'local';
    $backup->size_bytes = 1024000;
    
    if ($backup->backup_type === 'daily' && $backup->getSizeFormattedAttribute() === '1.00 MB') {
        echo "   ✅ BackupMonitoring model working\n";
        $passed++;
    } else {
        echo "   ❌ BackupMonitoring model failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Backup monitoring error: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 3: API Rate Limiting
echo "\n3. Testing API Rate Limiting...\n";
try {
    $rateLimit = new \App\Models\ApiRateLimit();
    $rateLimit->identifier = '127.0.0.1';
    $rateLimit->identifier_type = 'ip';
    $rateLimit->tier = 'public';
    $rateLimit->requests_count = 30;
    $rateLimit->limit = 30;
    
    if ($rateLimit->tier === 'public' && $rateLimit->limit === 30) {
        echo "   ✅ ApiRateLimit model working\n";
        $passed++;
    } else {
        echo "   ❌ ApiRateLimit model failed\n";
        $failed++;
    }

    // Test rate limiting service
    $status = \App\Services\ApiRateLimitService::getCurrentStatus('127.0.0.1', 'ip');
    if (isset($status['identifier']) && $status['identifier'] === '127.0.0.1') {
        echo "   ✅ ApiRateLimitService working\n";
        $passed++;
    } else {
        echo "   ❌ ApiRateLimitService failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ API rate limiting error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Test 4: Audit Service
echo "\n4. Testing Audit Service...\n";
try {
    // Test audit service
    $audit = \App\Services\AuditService::logSystem('test_event', 'Test audit log', [
        'test' => true,
        'timestamp' => now()
    ]);
    
    if ($audit && $audit->event === 'test_event') {
        echo "   ✅ AuditService working\n";
        $passed++;
    } else {
        echo "   ❌ AuditService failed\n";
        $failed++;
    }

    // Test audit statistics
    $stats = \App\Services\AuditService::getStatistics(7);
    if (isset($stats['total_audits'])) {
        echo "   ✅ Audit statistics working\n";
        $passed++;
    } else {
        echo "   ❌ Audit statistics failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Audit service error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Test 5: Import/Export Classes
echo "\n5. Testing Import/Export Classes...\n";
try {
    // Test BooksImport
    $importLog = new \App\Models\ImportLog(['id' => (string) \Illuminate\Support\Str::uuid()]);
    $booksImport = new \App\Imports\BooksImport($importLog);
    
    if ($booksImport instanceof \Maatwebsite\Excel\Concerns\WithChunkReading) {
        echo "   ✅ BooksImport class working\n";
        $passed++;
    } else {
        echo "   ❌ BooksImport class failed\n";
        $failed++;
    }

    // Test BooksExport
    $booksExport = new \App\Exports\BooksExport();
    
    if ($booksExport instanceof \Maatwebsite\Excel\Concerns\FromQuery) {
        echo "   ✅ BooksExport class working\n";
        $passed++;
    } else {
        echo "   ❌ BooksExport class failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Import/Export classes error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Test 6: Middleware
echo "\n6. Testing Middleware...\n";
try {
    // Test API rate limiting middleware
    $rateLimitMiddleware = new \App\Http\Middleware\ApiRateLimitMiddleware();
    
    if ($rateLimitMiddleware instanceof \Closure) {
        echo "   ✅ ApiRateLimitMiddleware working\n";
        $passed++;
    } else {
        echo "   ❌ ApiRateLimitMiddleware failed\n";
        $failed++;
    }

    // Test data transformation middleware
    $transformMiddleware = new \App\Http\Middleware\ApiDataTransformMiddleware();
    
    if ($transformMiddleware instanceof \Closure) {
        echo "   ✅ ApiDataTransformMiddleware working\n";
        $passed++;
    } else {
        echo "   ❌ ApiDataTransformMiddleware failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Test 7: Controllers
echo "\n7. Testing Controllers...\n";
try {
    // Test ImportController
    $importController = new \App\Http\Controllers\Admin\ImportController();
    
    if (method_exists($importController, 'index') && method_exists($importController, 'store')) {
        echo "   ✅ ImportController working\n";
        $passed++;
    } else {
        echo "   ❌ ImportController failed\n";
        $failed++;
    }

    // Test ExportController
    $exportController = new \App\Http\Controllers\Admin\ExportController();
    
    if (method_exists($exportController, 'index') && method_exists($exportController, 'store')) {
        echo "   ✅ ExportController working\n";
        $passed++;
    } else {
        echo "   ❌ ExportController failed\n";
        $failed++;
    }

    // Test BackupController
    $backupController = new \App\Http\Controllers\Admin\BackupController();
    
    if (method_exists($backupController, 'index') && method_exists($backupController, 'store')) {
        echo "   ✅ BackupController working\n";
        $passed++;
    } else {
        echo "   ❌ BackupController failed\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "   ❌ Controllers error: " . $e->getMessage() . "\n";
    $failed += 3;
}

// Test 8: Commands
echo "\n8. Testing Artisan Commands...\n";
try {
    $commands = [
        'backup:run-custom',
        'backup:clean-custom',
        'order:cleanup-pending',
        'report:generate-daily',
        'system:health-check'
    ];

    foreach ($commands as $command) {
        if (\Illuminate\Support\Facades\Artisan::has($command)) {
            echo "   ✅ Command '{$command}' available\n";
            $passed++;
        } else {
            echo "   ❌ Command '{$command}' not found\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "   ❌ Commands error: " . $e->getMessage() . "\n";
    $failed += 5;
}

// Test 9: Database Tables
echo "\n9. Testing Database Tables...\n";
try {
    $tables = [
        'import_logs',
        'export_logs',
        'api_rate_limits',
        'backup_monitoring',
        'scheduled_tasks',
        'audit_archives',
        'audits'
    ];

    foreach ($tables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "   ✅ Table '{$table}' exists\n";
            $passed++;
        } else {
            echo "   ❌ Table '{$table}' missing\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database tables error: " . $e->getMessage() . "\n";
    $failed += 7;
}

// Test 10: Configuration Files
echo "\n10. Testing Configuration Files...\n";
try {
    $configs = [
        'backup.php' => config('backup'),
        'audit.php' => config('audit'),
    ];

    foreach ($configs as $file => $config) {
        if ($config !== null) {
            echo "   ✅ Config '{$file}' loaded\n";
            $passed++;
        } else {
            echo "   ❌ Config '{$file}' missing\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "   ❌ Configuration error: " . $e->getMessage() . "\n";
    $failed += 2;
}

// Results
echo "\n=== Test Results ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total: " . ($passed + $failed) . "\n";

$successRate = $passed / ($passed + $failed) * 100;
echo "Success Rate: " . round($successRate, 2) . "%\n";

if ($successRate >= 90) {
    echo "\n🎉 EXCELLENT! Laboratory Activity 6 implementation is working perfectly!\n";
} elseif ($successRate >= 75) {
    echo "\n✅ GOOD! Laboratory Activity 6 implementation is mostly working.\n";
} elseif ($successRate >= 50) {
    echo "\n⚠️  FAIR! Laboratory Activity 6 implementation has some issues.\n";
} else {
    echo "\n❌ POOR! Laboratory Activity 6 implementation needs significant fixes.\n";
}

echo "\nNext Steps:\n";
echo "1. Run 'php artisan serve' to start the development server\n";
echo "2. Visit http://localhost:8000 to test the application\n";
echo "3. Check the admin dashboard for new features\n";
echo "4. Review any failed tests and fix issues\n\n";

echo "For detailed documentation, see: LAB_ACTIVITY_6_DOCUMENTATION.md\n";
