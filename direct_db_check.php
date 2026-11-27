<?php
// Direct database connection check for XAMPP MySQL
echo "=== DIRECT DATABASE CHECK ===\n\n";

// XAMPP MySQL default settings
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';

// Try common database names
$databases = ['laravel', 'activity3', 'pageturner', 'test'];

$connected = false;
$activeDb = '';

foreach ($databases as $database) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Connected to database: $database\n";
        $connected = true;
        $activeDb = $database;
        break;
    } catch (PDOException $e) {
        echo "❌ Failed to connect to $database: " . $e->getMessage() . "\n";
    }
}

if (!$connected) {
    echo "\n❌ Could not connect to any database!\n";
    echo "💡 Make sure:\n";
    echo "   1. XAMPP MySQL is running\n";
    echo "   2. Database exists in phpMyAdmin\n";
    echo "   3. .env file has correct database name\n";
    exit;
}

echo "\n🔍 Checking users table...\n";

try {
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "❌ Users table doesn't exist!\n";
        echo "💡 Run: php artisan migrate\n";
        exit;
    }
    
    echo "✅ Users table found\n";
    
    // Check for admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@pageturner.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "📝 Admin user found:\n";
        echo "   ID: " . $admin['id'] . "\n";
        echo "   Name: " . $admin['name'] . "\n";
        echo "   Email: " . $admin['email'] . "\n";
        echo "   Role: " . $admin['role'] . "\n";
        echo "   Email Verified: " . ($admin['email_verified_at'] ? 'Yes' : 'No') . "\n";
        
        // Test password
        if (password_verify('admin123', $admin['password'])) {
            echo "   ✅ Password 'admin123' works!\n";
        } else {
            echo "   ❌ Password 'admin123' failed!\n";
            echo "   🔧 Updating password...\n";
            
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ?, email_verified_at = ? WHERE email = ?");
            $updateStmt->execute([$newHash, date('Y-m-d H:i:s'), 'admin@pageturner.com']);
            
            echo "   ✅ Password updated!\n";
        }
    } else {
        echo "❌ Admin user not found!\n";
        echo "🔧 Creating admin user...\n";
        
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([
            'Admin User',
            'admin@pageturner.com',
            $newHash,
            'admin',
            $now,
            $now,
            $now
        ]);
        
        echo "✅ Admin user created!\n";
    }
    
    // Final verification
    $verifyStmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $verifyStmt->execute(['admin@pageturner.com']);
    $finalAdmin = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($finalAdmin && password_verify('admin123', $finalAdmin['password'])) {
        echo "\n🎉 ADMIN ACCOUNT READY!\n";
        echo "========================\n";
        echo "📧 Email: admin@pageturner.com\n";
        echo "🔑 Password: admin123\n";
        echo "🎭 Role: admin\n";
        echo "✉️  Email Verified: Yes\n";
        echo "🗄️  Database: $activeDb\n";
        echo "\n🚀 TRY LOGIN NOW!\n";
    } else {
        echo "\n❌ Something went wrong!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
