<?php
// Check MySQL connection and admin account
echo "=== XAMPP MYSQL CHECK ===\n\n";

try {
    // Check if Laravel can connect to database
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrap successful\n";
    
    // Test database connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "✅ Database connection: SUCCESS\n";
        echo "📋 Database: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "\n";
    } catch (\Exception $e) {
        echo "❌ Database connection: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        return;
    }
    
    // Check admin user
    $admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();
    
    if ($admin) {
        echo "\n✅ Admin user found:\n";
        echo "   Name: " . $admin->name . "\n";
        echo "   Email: " . $admin->email . "\n";
        echo "   Role: " . $admin->role . "\n";
        echo "   Email Verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "\n";
        echo "   Created: " . $admin->created_at . "\n";
        
        // Test password
        if (\Illuminate\Support\Facades\Hash::check('admin123', $admin->password)) {
            echo "   ✅ Password verification: PASSED\n";
            echo "\n🎉 LOGIN SHOULD WORK!\n";
        } else {
            echo "   ❌ Password verification: FAILED\n";
            echo "   🔧 Fixing password...\n";
            
            $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
            $admin->email_verified_at = now();
            $admin->save();
            
            echo "   ✅ Password fixed!\n";
        }
    } else {
        echo "\n❌ Admin user not found!\n";
        echo "🔧 Creating admin user...\n";
        
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@pageturner.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        echo "✅ Admin user created!\n";
    }
    
    echo "\n🚀 TRY LOGIN NOW:\n";
    echo "   URL: http://127.0.0.1:8000/login\n";
    echo "   Email: admin@pageturner.com\n";
    echo "   Password: admin123\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 Possible issues:\n";
    echo "   1. XAMPP MySQL is not running\n";
    echo "   2. Database doesn't exist\n";
    echo "   3. Wrong database credentials in .env\n";
    echo "   4. Laravel not properly configured\n";
}
?>
