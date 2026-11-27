<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== AI Configuration Test ===\n\n";

// Check if API key is configured
$apiKey = config('ai.providers.openai.api_key');
echo "OpenAI API Key: " . ($apiKey ? "SET (length: " . strlen($apiKey) . ")" : "NOT SET") . "\n";

if ($apiKey) {
    echo "First 10 chars: " . substr($apiKey, 0, 10) . "...\n";
}

echo "\nDefault Provider: " . config('ai.default_provider') . "\n";
echo "Fallback Enabled: " . (config('ai.fallback_enabled') ? "YES" : "NO") . "\n";
echo "Fallback Chain: " . implode(', ', config('ai.fallback_chain')) . "\n";

// Test if OpenAI client can be created
echo "\n=== Testing OpenAI Connection ===\n";
try {
    $guzzleClient = new \GuzzleHttp\Client([
        'verify' => config('app.env') === 'local' ? false : true,
    ]);

    $client = OpenAI::factory()
        ->withHttpClient($guzzleClient)
        ->withApiKey($apiKey)
        ->make();

    echo "✓ OpenAI client created successfully\n";

    // Try a simple test request
    echo "\n=== Testing API Call ===\n";
    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'user', 'content' => 'Say "test successful"']
        ],
        'max_tokens' => 10,
    ]);

    echo "✓ API call successful\n";
    echo "Response: " . $response->choices[0]->message->content . "\n";
    echo "Tokens used: " . $response->usage->totalTokens . "\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
