<?php

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "Checking .env file for AI configuration...\n\n";
    $content = file_get_contents($envFile);
    
    if (str_contains($content, 'OPENAI_API_KEY=')) {
        echo "✓ OPENAI_API_KEY found\n";
        // Extract the value
        preg_match('/OPENAI_API_KEY=(.*)/', $content, $matches);
        echo "  Value: " . ($matches[1] ?? 'empty') . "\n";
    } else {
        echo "✗ OPENAI_API_KEY not found\n";
    }
    
    if (str_contains($content, 'AI_DEFAULT_PROVIDER=')) {
        echo "✓ AI_DEFAULT_PROVIDER found\n";
    } else {
        echo "✗ AI_DEFAULT_PROVIDER not found\n";
    }
    
    if (str_contains($content, 'AI_FALLBACK_ENABLED=')) {
        echo "✓ AI_FALLBACK_ENABLED found\n";
    } else {
        echo "✗ AI_FALLBACK_ENABLED not found\n";
    }
} else {
    echo ".env file not found!\n";
}
