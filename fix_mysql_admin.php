<?php
// Fix admin account for MySQL database
echo "=== FIXING ADMIN ACCOUNT FOR MYSQL ===\n\n";

try {
    // MySQL connection (adjust credentials as needed)
    $host = '127.0.0.1';
    $port = '3306';
    $database = 'laravel'; // Change if your DB name is different
    $username = 'root';
    $password = ''; // XAMPP default is empty
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL database: $database\n\n";
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@pageturner.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "📝 Admin user found, updating...\n";
        
        // Update existing admin
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, email_verified_at = ?, updated_at = ? WHERE email = ?");
        $updateStmt->execute([$hash, $now, $now, 'admin@pageturner.com']);
        
        echo "✅ Admin updated successfully!\n";
    } else {
        echo "📝 Admin user not found, creating...\n";
        
        // Create new admin
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([
            'Admin User',
            'admin@pageturner.com',
            $hash,
            'admin',
            $now,
            $now,
            $now
        ]);
        
        echo "✅ Admin created successfully!\n";
    }
    
    // Verify the admin account
    $verifyStmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $verifyStmt->execute(['admin@pageturner.com']);
    $admin = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify('admin123', $admin['password'])) {
        echo "\n🎉 ADMIN ACCOUNT READY!\n";
        echo "========================\n";
        echo "📧 Email: admin@pageturner.com\n";
        echo "🔑 Password: admin123\n";
        echo "🎭 Role: admin\n";
        echo "✉️  Email Verified: Yes\n";
        echo "📅 Created: " . $admin['created_at'] . "\n";
        echo "\n🔐 TWO-FACTOR AUTHENTICATION:\n";
        echo "   ✅ You can now enable 2FA!\n";
        echo "   ✅ Email is already verified!\n";
        echo "\n🚀 TRY LOGIN NOW:\n";
        echo "   URL: http://127.0.0.1:8000/login\n";
        echo "   Email: admin@pageturner.com\n";
        echo "   Password: admin123\n";
    } else {
        echo "\n❌ Something went wrong with admin creation\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n💡 Possible solutions:\n";
    echo "   1. Check your MySQL credentials in .env file\n";
    echo "   2. Make sure MySQL is running in XAMPP\n";
    echo "   3. Verify database name exists\n";
    echo "   4. Update this script with correct DB credentials\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
