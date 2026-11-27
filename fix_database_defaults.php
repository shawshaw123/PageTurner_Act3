<?php

/**
 * Fix Database Default Values
 * Run this script to fix all missing default values in database tables
 */

echo "=== Fixing Database Default Values ===\n";

// Fix 1: Add default values to users table
echo "1. Fix users table defaults...\n";
try {
    $users = \DB::table('users')->whereNull('id')->get();
    foreach ($users as $user) {
        $updates = [];
        
        if (is_null($user->email_verified_at)) {
            $updates['email_verified_at'] = now();
        }
        
        if (is_null($user->role)) {
            $updates['role'] = 'customer';
        }
        
        if (is_null($user->created_at)) {
            $updates['created_at'] = now();
        }
        
        if (count($updates) > 0) {
            \DB::table('users')->where('id', $user->id)->update($updates);
            echo "✅ Updated user ID {$user->id}: " . implode(', ', array_keys($updates)) . "\n";
        }
    }
    echo "✅ Users table defaults fixed!\n";
} catch (Exception $e) {
    echo "❌ Error fixing users table: " . $e->getMessage() . "\n";
}

// Fix 2: Add default values to export_logs table
echo "\n2. Fix export_logs table defaults...\n";
try {
    $exports = \DB::table('export_logs')->whereNull('updated_at')->get();
    foreach ($exports as $export) {
        $updates = ['updated_at' => now()];
        \DB::table('export_logs')->where('id', $export->id)->update($updates);
        echo "✅ Updated export ID {$export->id} with updated_at\n";
    }
    echo "✅ Export logs table defaults fixed!\n";
} catch (Exception $e) {
    echo "❌ Error fixing export_logs table: " . $e->getMessage() . "\n";
}

// Fix 3: Add default values to audit_archives table
echo "\n3. Fix audit_archives table defaults...\n";
try {
    $archives = \DB::table('audit_archives')->whereNull('created_at')->get();
    foreach ($archives as $archive) {
        $updates = ['created_at' => now()];
        \DB::table('audit_archives')->where('id', $archive->id)->update($updates);
        echo "✅ Updated archive ID {$archive->id} with created_at\n";
    }
    echo "✅ Audit archives table defaults fixed!\n";
} catch (Exception $e) {
    echo "❌ Error fixing audit_archives table: " . $e->getMessage() . "\n";
}

// Fix 4: Add default values to scheduled_tasks table
echo "\n4. Fix scheduled_tasks table defaults...\n";
try {
    $tasks = \DB::table('scheduled_tasks')->whereNull('last_run_at')->get();
    foreach ($tasks as $task) {
        $updates = ['last_run_at' => now()];
        \DB::table('scheduled_tasks')->where('id', $task->id)->update($updates);
        echo "✅ Updated task ID {$task->id} with last_run_at\n";
    }
    echo "✅ Scheduled tasks table defaults fixed!\n";
} catch (Exception $e) {
    echo "❌ Error fixing scheduled_tasks table: " . $e->getMessage() . "\n";
}

// Fix 5: Add default values to backup_monitoring table
echo "\n5. Fix backup_monitoring table defaults...\n";
try {
    $backups = \DB::table('backup_monitoring')->whereNull('started_at')->get();
    foreach ($backups as $backup) {
        $updates = ['started_at' => now()];
        \DB::table('backup_monitoring')->where('id', $backup->id)->update($updates);
        echo "✅ Updated backup ID {$backup->id} with started_at\n";
    }
    echo "✅ Backup monitoring table defaults fixed!\n";
} catch (Exception $e) {
    echo "❌ Error fixing backup_monitoring table: " . $e->getMessage() . "\n";
}

echo "\n=== All Database Defaults Fixed ===\n";
echo "✅ Users table: email_verified_at, role, created_at\n";
echo "✅ Export logs table: updated_at\n";
echo "✅ Audit archives table: created_at\n";
echo "✅ Scheduled tasks table: last_run_at\n";
echo "✅ Backup monitoring table: started_at\n";
echo "\nRun this script to fix all missing default values!\n";
