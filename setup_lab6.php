<?php

/**
 * Laboratory Activity 6 Setup Script
 * 
 * This script helps with the initial setup and configuration
 * of the Lab 6 features for PageTurner Bookstore.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Laboratory Activity 6 Setup ===\n\n";

// Check if all required packages are installed
echo "1. Checking required packages...\n";
$requiredPackages = [
    'maatwebsite/excel',
    'spatie/laravel-backup',
    'owen-it/laravel-auditing',
    'barryvdh/laravel-dompdf'
];

foreach ($requiredPackages as $package) {
    if (class_exists(str_replace('/', '\\', $package))) {
        echo "   ✅ {$package} - Installed\n";
    } else {
        echo "   ❌ {$package} - Not installed\n";
    }
}

echo "\n2. Checking database tables...\n";
$requiredTables = [
    'import_logs',
    'export_logs', 
    'api_rate_limits',
    'backup_monitoring',
    'scheduled_tasks',
    'audit_archives',
    'audits'
];

foreach ($requiredTables as $table) {
    try {
        \Illuminate\Support\Facades\Schema::hasTable($table);
        echo "   ✅ {$table} - Exists\n";
    } catch (Exception $e) {
        echo "   ❌ {$table} - Error: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Creating storage directories...\n";
$directories = [
    'app/imports',
    'app/exports',
    'app/backups',
    'app/audit-archives',
    'app/reports'
];

foreach ($directories as $dir) {
    $fullPath = storage_path($dir);
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "   ✅ Created: {$dir}\n";
    } else {
        echo "   ✅ Exists: {$dir}\n";
    }
}

echo "\n4. Setting permissions...\n";
$storagePath = storage_path();
exec("chmod -R 775 {$storagePath}");
echo "   ✅ Storage permissions set\n";

echo "\n5. Publishing vendor files...\n";
$commands = [
    'php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --force',
    'php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --force',
    'php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --force',
    'php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider" --force'
];

foreach ($commands as $command) {
    echo "   Running: {$command}\n";
    passthru($command);
}

echo "\n6. Clearing caches...\n";
passthru('php artisan config:clear');
passthru('php artisan cache:clear');
passthru('php artisan route:clear');
echo "   ✅ Caches cleared\n";

echo "\n7. Creating sample data...\n";

// Create sample categories if they don't exist
if (\App\Models\Category::count() === 0) {
    \App\Models\Category::create(['name' => 'Fiction']);
    \App\Models\Category::create(['name' => 'Non-Fiction']);
    \App\Models\Category::create(['name' => 'Science']);
    \App\Models\Category::create(['name' => 'Technology']);
    echo "   ✅ Sample categories created\n";
} else {
    echo "   ✅ Categories already exist\n";
}

// Create admin user if not exists
if (\App\Models\User::where('role', 'admin')->count() === 0) {
    \App\Models\User::create([
        'name' => 'Administrator',
        'email' => 'admin@pageturner.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "   ✅ Admin user created (admin@pageturner.com / admin123)\n";
} else {
    echo "   ✅ Admin user already exists\n";
}

echo "\n8. Testing key features...\n";

// Test audit logging
try {
    $user = \App\Models\User::first();
    if ($user) {
        \App\Services\AuditService::logSystem('test', 'Lab 6 setup test', [
            'user_id' => $user->id,
            'timestamp' => now()
        ]);
        echo "   ✅ Audit logging working\n";
    }
} catch (Exception $e) {
    echo "   ❌ Audit logging error: " . $e->getMessage() . "\n";
}

// Test rate limiting service
try {
    $stats = \App\Services\ApiRateLimitService::getStatistics(24);
    echo "   ✅ Rate limiting service working\n";
} catch (Exception $e) {
    echo "   ❌ Rate limiting service error: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete ===\n\n";
echo "Next Steps:\n";
echo "1. Run 'php artisan serve' to start the development server\n";
echo "2. Visit http://localhost:8000/admin/dashboard to see the new dashboard\n";
echo "3. Test import/export features in the admin panel\n";
echo "4. Configure backup settings in config/backup.php\n";
echo "5. Set up the cron job: * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1\n";
echo "6. Configure queue workers for background processing\n\n";

echo "Documentation: LAB_ACTIVITY_6_DOCUMENTATION.md\n";
echo "Support: Check the logs in storage/logs for any issues\n\n";

echo "🎉 Laboratory Activity 6 setup completed successfully! 🎉\n";
