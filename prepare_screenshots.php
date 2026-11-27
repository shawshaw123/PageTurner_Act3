<?php

/**
 * Screenshot Preparation Script
 * This script helps prepare the system for capturing Lab 8 screenshots
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Lab 8 Screenshot Preparation ===\n\n";

// 1. Check AI configuration
echo "1. Checking AI Configuration...\n";
$apiKey = config('ai.providers.openai.api_key');
$defaultProvider = config('ai.default_provider');
$fallbackChain = config('ai.fallback_chain');

echo "   Default Provider: " . ($defaultProvider ?: 'NOT SET') . "\n";
echo "   OpenAI API Key: " . ($apiKey ? "SET (length: " . strlen($apiKey) . ")" : "NOT SET") . "\n";
echo "   Fallback Chain: " . implode(' → ', $fallbackChain) . "\n\n";

// 2. Check database tables
echo "2. Checking Database Tables...\n";
try {
    $convCount = \DB::table('ai_conversations')->count();
    $msgCount = \DB::table('ai_messages')->count();
    $logCount = \DB::table('ai_usage_logs')->count();
    
    echo "   ai_conversations: $convCount records\n";
    echo "   ai_messages: $msgCount records\n";
    echo "   ai_usage_logs: $logCount records\n\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}

// 3. Create test conversation if none exists
echo "3. Creating Test Data (if needed)...\n";
if ($convCount == 0) {
    echo "   Creating test conversation...\n";
    $convId = \DB::table('ai_conversations')->insertGetId([
        'user_id' => null,
        'session_id' => 'test-session-' . time(),
        'title' => 'Test Conversation for Screenshots',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    \DB::table('ai_messages')->insert([
        'conversation_id' => $convId,
        'role' => 'user',
        'content' => 'Hello, can you recommend me a good fiction book?',
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'tokens_used' => 25,
        'cost_estimate' => 0.0000075,
        'response_time' => 1.2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    \DB::table('ai_messages')->insert([
        'conversation_id' => $convId,
        'role' => 'assistant',
        'content' => 'I\'d be happy to help! Based on our catalog, we have excellent fiction books. Some popular options include classic literature, contemporary novels, and genre fiction. What type of fiction interests you most?',
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'tokens_used' => 45,
        'cost_estimate' => 0.0000135,
        'response_time' => 1.8,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "   ✓ Test conversation created\n\n";
} else {
    echo "   ✓ Test data already exists\n\n";
}

// 4. Check queue configuration
echo "4. Checking Queue Configuration...\n";
$queueConnection = config('queue.default');
echo "   Queue Connection: $queueConnection\n";
echo "   Queue Driver: " . config("queue.connections.$queueConnection.driver") . "\n\n";

// 5. Check admin user
echo "5. Checking Admin User...\n";
$admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();
if ($admin) {
    echo "   ✓ Admin user exists\n";
    echo "   Email: admin@pageturner.com\n";
    echo "   Password: admin123\n\n";
} else {
    echo "   ✗ Admin user NOT found\n";
    echo "   Creating admin user...\n";
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@pageturner.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "   ✓ Admin user created\n\n";
}

// 6. Check book data for RAG
echo "6. Checking Book Data for RAG...\n";
$bookCount = \App\Models\Book::count();
$categoryCount = \DB::table('categories')->count();
echo "   Total Books: $bookCount\n";
echo "   Total Categories: $categoryCount\n\n";

if ($bookCount == 0) {
    echo "   ⚠ WARNING: No books in database. RAG will not work properly.\n";
    echo "   Run: php artisan db:seed --class=BookSeeder\n\n";
}

// 7. Display URLs for screenshots
echo "=== Screenshot URLs ===\n\n";
echo "Chat Interface:\n";
echo "   http://localhost:8000/ai/chat\n\n";
echo "Admin Dashboard:\n";
echo "   http://localhost:8000/admin/ai/dashboard\n\n";
echo "Login Page:\n";
echo "   http://localhost:8000/login\n\n";

// 8. Display commands to run
echo "=== Commands to Run ===\n\n";
echo "Terminal 1 (Development Server):\n";
echo "   php artisan serve\n\n";
echo "Terminal 2 (Queue Worker):\n";
echo "   php artisan queue:work\n\n";
echo "Terminal 3 (Screenshot Prep - this script):\n";
echo "   php prepare_screenshots.php\n\n";

echo "=== Preparation Complete ===\n";
echo "Follow the SCREENSHOT_CAPTURE_GUIDE.md for detailed instructions.\n";
