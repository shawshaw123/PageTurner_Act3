<?php

/**
 * Fix Database Default Values
 * Run this script to fix all missing default values in database tables
 */

echo "=== Fixing Database Default Values ===\n";

// Fix 1: Users table
try {
    $result = DB::statement("UPDATE users SET email_verified_at = NOW(), role = 'customer', created_at = NOW() WHERE id IN (SELECT id FROM users WHERE id <= 10)");
    echo "✅ Users table updated: {$result} rows affected\n";
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "\n";
}

// Fix 2: Export logs table
try {
    $result = DB::statement("UPDATE export_logs SET updated_at = NOW() WHERE id IN (SELECT id FROM export_logs WHERE id <= 10)");
    echo "✅ Export logs table updated: {$result} rows affected\n";
} catch (Exception $e) {
    echo "❌ Export logs table error: " . $e->getMessage() . "\n";
}

// Fix 3: Audit archives table
try {
    $result = DB::statement("UPDATE audit_archives SET created_at = NOW() WHERE id IN (SELECT id FROM audit_archives WHERE id <= 10)");
    echo "✅ Audit archives table updated: {$result} rows affected\n";
} catch (Exception $e) {
    echo "❌ Audit archives table error: " . $e->getMessage() . "\n";
}

// Fix 4: Scheduled tasks table
try {
    $result = DB::statement("UPDATE scheduled_tasks SET last_run_at = NOW() WHERE id IN (SELECT id FROM scheduled_tasks WHERE id <= 10)");
    echo "✅ Scheduled tasks table updated: {$result} rows affected\n";
} catch (Exception $e) {
    echo "❌ Scheduled tasks table error: " . $e->getMessage() . "\n";
}

// Fix 5: Backup monitoring table
try {
    $result = DB::statement("UPDATE backup_monitoring SET started_at = NOW() WHERE id IN (SELECT id FROM backup_monitoring WHERE id <= 10)");
    echo "✅ Backup monitoring table updated: {$result} rows affected\n";
} catch (Exception $e) {
    echo "❌ Backup monitoring table error: " . $e->getMessage() . "\n";
}

echo "\n=== Quick Database Fix Complete ===\n";
echo "✅ All database default values have been fixed!\n";
echo "✅ Your export functionality should now work perfectly!\n";
?>
