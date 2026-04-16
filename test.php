<?php
echo "PHP Test Script Running...\n";
echo "Current directory: " . __DIR__ . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Test database connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pageturner_bookstore", 'root', '');
    echo "✅ Database connection successful\n";
    
    // Check reviews table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Reviews table found with {$result['count']} reviews\n";
    
    // Check status column
    $stmt = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'status'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Status column exists\n";
    } else {
        echo "❌ Status column missing\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "Test completed!\n";
?>
