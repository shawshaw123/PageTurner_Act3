<?php

/**
 * XAMPP Environment Test Script
 * Tests all XAMPP-specific configurations for Lab 6
 */

echo "=== XAMPP Environment Test ===\n\n";

// Test 1: PHP Version
echo "1. PHP Version:\n";
echo "   Current: " . PHP_VERSION . "\n";
echo "   Required: 8.2+\n";
echo "   Status: " . (version_compare(PHP_VERSION, '8.2.0', '>=') ? '✓ PASS' : '✗ FAIL') . "\n\n";

// Test 2: Required Extensions
echo "2. Required PHP Extensions:\n";
$extensions = [
    'pdo_mysql' => 'MySQL Database',
    'mbstring' => 'Multi-byte String',
    'openssl' => 'OpenSSL',
    'curl' => 'cURL',
    'fileinfo' => 'File Information',
    'gd' => 'GD Graphics',
    'zip' => 'ZIP Archive',
    'json' => 'JSON',
    'tokenizer' => 'Tokenizer'
];

foreach ($extensions as $ext => $name) {
    $status = extension_loaded($ext) ? '✓ PASS' : '✗ FAIL';
    echo "   $name ({$ext}): $status\n";
}
echo "\n";

// Test 3: Database Connection
echo "3. Database Connection:\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pageturner_bookstore', 'root', '');
    echo "   MySQL Connection: ✓ PASS\n";
    
    // Test if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['users', 'books', 'orders', 'categories', 'import_logs', 'export_logs', 'audits'];
    
    foreach ($requiredTables as $table) {
        $status = in_array($table, $tables) ? '✓ PASS' : '✗ FAIL';
        echo "   Table '$table': $status\n";
    }
} catch (PDOException $e) {
    echo "   MySQL Connection: ✗ FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: File Permissions
echo "4. Storage Directory Permissions:\n";
$directories = [
    'storage/app',
    'storage/app/imports',
    'storage/app/exports',
    'storage/app/backups',
    'storage/app/audit-archives',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✓ PASS' : '✗ FAIL';
        echo "   $dir: $writable\n";
    } else {
        echo "   $dir: ✗ MISSING\n";
    }
}
echo "\n";

// Test 5: Memory and Upload Limits
echo "5. PHP Configuration:\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "   Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   Post Max Size: " . ini_get('post_max_size') . "\n\n";

// Test 6: Laravel Environment
echo "6. Laravel Environment:\n";
if (function_exists('app')) {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "   Laravel Version: " . app()->version() . "\n";
    echo "   Environment: " . app()->environment() . "\n";
    echo "   Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
    echo "   App URL: " . config('app.url') . "\n";
} else {
    echo "   Laravel: ✗ FAIL - Could not bootstrap\n";
}
echo "\n";

// Test 7: Key Lab 6 Components
echo "7. Lab 6 Components Test:\n";

// Test if key files exist
$files = [
    'app/Imports/BooksImport.php' => 'Books Import Class',
    'app/Exports/BooksExport.php' => 'Books Export Class',
    'app/Services/AuditService.php' => 'Audit Service',
    'app/Services/ApiRateLimitService.php' => 'API Rate Limit Service',
    'app/Http/Middleware/ApiRateLimitMiddleware.php' => 'Rate Limit Middleware',
    'app/Http/Middleware/ApiDataTransformMiddleware.php' => 'Data Transform Middleware',
    'config/backup.php' => 'Backup Configuration',
    'config/audit.php' => 'Audit Configuration',
    'app/Console/Commands/BackupRunCommand.php' => 'Backup Command',
    'app/Console/Commands/GenerateDailyReportCommand.php' => 'Daily Report Command'
];

foreach ($files as $file => $name) {
    $status = file_exists($file) ? '✓ PASS' : '✗ FAIL';
    echo "   $name: $status\n";
}
echo "\n";

// Test 8: Queue Configuration
echo "8. Queue Configuration:\n";
if (function_exists('app')) {
    $queueConnection = config('queue.default');
    echo "   Queue Connection: $queueConnection\n";
    
    if ($queueConnection === 'database') {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=pageturner_bookstore', 'root', '');
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $hasJobsTable = in_array('jobs', $tables);
            $hasFailedJobsTable = in_array('failed_jobs', $tables);
            
            echo "   Jobs Table: " . ($hasJobsTable ? '✓ PASS' : '✗ FAIL') . "\n";
            echo "   Failed Jobs Table: " . ($hasFailedJobsTable ? '✓ PASS' : '✗ FAIL') . "\n";
        } catch (Exception $e) {
            echo "   Queue Tables: ✗ FAIL - " . $e->getMessage() . "\n";
        }
    }
}
echo "\n";

echo "=== Test Complete ===\n\n";

echo "Quick Start Commands:\n";
echo "1. Run setup: xampp_setup.bat\n";
echo "2. Start queue: start_queue.bat\n";
echo "3. Create scheduler: create_scheduler_task.bat\n";
echo "4. Start server: php artisan serve\n\n";

echo "Access URLs:\n";
echo "- Development Server: http://localhost:8000\n";
echo "- XAMPP Apache: http://localhost/Activity4\n";
echo "- Admin Dashboard: http://localhost:8000/admin/dashboard\n\n";

echo "Default Admin Login:\n";
echo "- Email: admin@pageturner.com\n";
echo "- Password: admin123\n\n";
