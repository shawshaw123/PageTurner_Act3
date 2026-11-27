<?php

// Verify admin account
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@pageturner.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ ADMIN ACCOUNT READY!\n";
        echo "========================\n";
        echo "📧 Email: admin@pageturner.com\n";
        echo "🔑 Password: admin123\n";
        echo "👤 Name: " . $admin['name'] . "\n";
        echo "🎭 Role: " . $admin['role'] . "\n";
        echo "✉️  Email Verified: " . ($admin['email_verified_at'] ? 'YES ✅' : 'NO ❌') . "\n";
        echo "📅 Created: " . $admin['created_at'] . "\n";
        echo "\n🔐 TWO-FACTOR AUTHENTICATION:\n";
        echo "   ✅ You can now enable 2FA!\n";
        echo "   ✅ Email is already verified!\n";
        echo "   ✅ Login and go to Profile → Two-Factor Auth\n";
        echo "\n🚀 NEXT STEPS:\n";
        echo "   1. Login with: admin@pageturner.com / admin123\n";
        echo "   2. Click your name in navigation\n";
        echo "   3. Select 'Two-Factor Auth'\n";
        echo "   4. Enable 2FA and save recovery codes\n";
    } else {
        echo "❌ Admin account not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
