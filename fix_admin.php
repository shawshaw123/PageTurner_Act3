<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Find or create admin user
$admin = User::where('email', 'admin@pageturner.com')->first();

if (!$admin) {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@pageturner.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "✅ Admin user created!\n";
} else {
    // Update existing admin
    $admin->update([
        'password' => Hash::make('admin123'),
        'email_verified_at' => now(),
    ]);
    echo "✅ Admin user updated!\n";
}

echo "📧 Email: admin@pageturner.com\n";
echo "🔑 Password: admin123\n";
echo "✉️  Email verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "\n";
echo "🔐 You can now enable 2FA!\n";

// Show all admin users
$allAdmins = User::where('role', 'admin')->get();
echo "\n📋 All Admin Users:\n";
foreach ($allAdmins as $adminUser) {
    echo "- {$adminUser->email} (Verified: " . ($adminUser->email_verified_at ? 'Yes' : 'No') . ")\n";
}
