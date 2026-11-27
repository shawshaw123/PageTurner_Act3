<?php
// Simple web-based admin check
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h2>Admin Account Check</h2>";

try {
    // Check database connection
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p>✅ Database connection: SUCCESS</p>";
    echo "<p>📋 Database: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
    
    // Check admin user
    $admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();
    
    if ($admin) {
        echo "<h3>✅ Admin User Found:</h3>";
        echo "<ul>";
        echo "<li>Name: " . $admin->name . "</li>";
        echo "<li>Email: " . $admin->email . "</li>";
        echo "<li>Role: " . $admin->role . "</li>";
        echo "<li>Email Verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "</li>";
        echo "<li>Created: " . $admin->created_at . "</li>";
        echo "</ul>";
        
        // Test password
        if (\Illuminate\Support\Facades\Hash::check('admin123', $admin->password)) {
            echo "<p style='color: green;'>✅ Password 'admin123' verification: PASSED</p>";
            echo "<p><strong>Login should work!</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Password 'admin123' verification: FAILED</p>";
            echo "<p>Fixing password...</p>";
            
            $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
            $admin->email_verified_at = now();
            $admin->save();
            
            echo "<p style='color: green;'>✅ Password fixed!</p>";
        }
    } else {
        echo "<h3>❌ Admin User Not Found!</h3>";
        echo "<p>Creating admin user...</p>";
        
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@pageturner.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        echo "<p style='color: green;'>✅ Admin user created!</p>";
    }
    
    echo "<h3>🔑 Login Credentials:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td>Email:</td><td>admin@pageturner.com</td></tr>";
    echo "<tr><td>Password:</td><td>admin123</td></tr>";
    echo "<tr><td>Role:</td><td>admin</td></tr>";
    echo "<tr><td>Email Verified:</td><td>Yes</td></tr>";
    echo "</table>";
    
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='/login'>Go to Login Page</a></li>";
    echo "<li>Login with credentials above</li>";
    echo "<li>Enable 2FA for security</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Check your XAMPP MySQL configuration</p>";
}

$kernel->terminate($request, $response);
?>
