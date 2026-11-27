<?php
// Simple database connection to fix review status
echo "=== SIMPLE REVIEW STATUS FIX ===\n\n";

try {
    // Connect to MySQL directly
    $host = '127.0.0.1';
    $port = '3306';
    $database = 'pageturner_bookstore';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n";
    
    // Check if status column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Status column doesn't exist! Running migration...\n";
        
        // Add status column
        $pdo->exec("ALTER TABLE reviews ADD COLUMN status VARCHAR(20) DEFAULT 'approved' AFTER comment");
        echo "✅ Status column added\n";
    } else {
        echo "✅ Status column exists\n";
    }
    
    // Update reviews without status
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE status IS NULL OR status = ''");
    $affected = $stmt->rowCount();
    
    echo "✅ Updated $affected reviews to 'approved' status\n";
    
    // Show status summary
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM reviews");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n📊 Status Summary:\n";
    echo "Total Reviews: {$result['total']}\n";
    echo "Approved: {$result['approved']}\n";
    echo "Rejected: {$result['rejected']}\n";
    echo "Pending: {$result['pending']}\n";
    
    // Show some sample reviews
    $stmt = $pdo->query("SELECT r.id, b.title as book_title, u.name as reviewer_name, r.rating, r.status 
                        FROM reviews r 
                        JOIN books b ON r.book_id = b.id 
                        JOIN users u ON r.user_id = u.id 
                        LIMIT 5");
    
    echo "\n📝 Sample Reviews:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Book: {$row['book_title']} | Reviewer: {$row['reviewer_name']} | Rating: {$row['rating']} | Status: {$row['status']}\n";
    }
    
    echo "\n🎉 Review status fix completed!\n";
    echo "Now admin should see approve/reject buttons on book pages.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n💡 Possible solutions:\n";
    echo "   1. Make sure XAMPP MySQL is running\n";
    echo "   2. Check database name: pageturner_bookstore\n";
    echo "   3. Verify MySQL credentials\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
