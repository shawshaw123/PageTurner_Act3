<?php

// Simple database connection and admin check
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@pageturner.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin user found:\n";
        echo "ID: " . $admin['id'] . "\n";
        echo "Name: " . $admin['name'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Email Verified: " . ($admin['email_verified_at'] ? 'Yes' : 'No') . "\n";
        echo "Created: " . $admin['created_at'] . "\n";
        
        // Update admin with verified email and known password
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $verifiedAt = date('Y-m-d H:i:s');
        
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, email_verified_at = ? WHERE email = ?");
        $updateStmt->execute([$newPassword, $verifiedAt, 'admin@pageturner.com']);
        
        echo "\n✅ Admin updated!\n";
        echo "🔑 New Password: admin123\n";
        echo "✉️  Email Verified: Yes\n";
        echo "🔐 You can now enable 2FA!\n";
    } else {
        echo "Admin user not found. Creating...\n";
        
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $verifiedAt = date('Y-m-d H:i:s');
        
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([
            'Admin User',
            'admin@pageturner.com', 
            $newPassword,
            'admin',
            $verifiedAt,
            $verifiedAt,
            $verifiedAt
        ]);
        
        echo "✅ Admin created!\n";
        echo "📧 Email: admin@pageturner.com\n";
        echo "🔑 Password: admin123\n";
        echo "✉️  Email Verified: Yes\n";
        echo "🔐 You can now enable 2FA!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
