<?php

/**
 * Fix AI Chatbot Configuration
 * This script checks if AI API keys are configured in .env and adds them if missing.
 */

$envFile = __DIR__ . '/.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

// Check if AI configuration exists
$hasOpenAIKey = str_contains($envContent, 'OPENAI_API_KEY=');
$hasGeminiKey = str_contains($envContent, 'GEMINI_API_KEY=');
$hasAIProvider = str_contains($envContent, 'AI_DEFAULT_PROVIDER=');

if (!$hasAIProvider) {
    $envContent .= "\n# AI Configuration\n";
    $envContent .= "AI_DEFAULT_PROVIDER=openai\n";
    $envContent .= "AI_FALLBACK_ENABLED=true\n";
}

if (!$hasOpenAIKey) {
    $envContent .= "OPENAI_API_KEY=\n";
    $envContent .= "OPENAI_MODEL=gpt-4o-mini\n";
}

if (!$hasGeminiKey) {
    $envContent .= "GEMINI_API_KEY=\n";
    $envContent .= "GEMINI_MODEL=gemini-1.5-flash\n";
}

$envContent .= "OLLAMA_ENABLED=false\n";
$envContent .= "OLLAMA_BASE_URL=http://localhost:11434\n";
$envContent .= "OLLAMA_MODEL=llama3.2\n";

// Save the updated .env file
file_put_contents($envFile, $envContent);

echo "AI configuration has been added to .env file.\n";
echo "Please add your OpenAI API key to the OPENAI_API_KEY variable in .env\n";
echo "You can get an API key from: https://platform.openai.com/api-keys\n";
echo "\nAfter adding the API key, run: php artisan config:clear\n";
