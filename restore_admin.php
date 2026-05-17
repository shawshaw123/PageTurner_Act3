<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Restoring Admin User ===\n\n";

// Check if admin exists
$admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();

if ($admin) {
    echo "Admin user found. Updating password...\n";
    $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
    $admin->email_verified_at = now();
    $admin->role = 'admin';
    $admin->save();
    echo "✓ Password updated to: admin123\n";
} else {
    echo "Admin user not found. Creating new admin...\n";
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@pageturner.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "✓ Admin user created\n";
}

echo "\n=== Login Credentials ===\n";
echo "Email: admin@pageturner.com\n";
echo "Password: admin123\n";
echo "Role: admin\n";
echo "\nLogin at: http://localhost:8000/login\n";
