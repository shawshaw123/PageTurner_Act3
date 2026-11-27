<?php

$envFile = __DIR__ . '/.env';
$content = file_get_contents($envFile);

// Check if AI config already exists
if (!str_contains($content, 'AI_DEFAULT_PROVIDER=')) {
    // Add AI configuration at the end
    $aiConfig = "\n\n# AI Configuration\n";
    $aiConfig .= "AI_DEFAULT_PROVIDER=openai\n";
    $aiConfig .= "AI_FALLBACK_ENABLED=true\n";
    $aiConfig .= "OPENAI_MODEL=gpt-4o-mini\n";
    $aiConfig .= "GEMINI_API_KEY=\n";
    $aiConfig .= "GEMINI_MODEL=gemini-1.5-flash\n";
    $aiConfig .= "OLLAMA_ENABLED=false\n";
    $aiConfig .= "OLLAMA_BASE_URL=http://localhost:11434\n";
    $aiConfig .= "OLLAMA_MODEL=llama3.2\n";
    
    file_put_contents($envFile, $content . $aiConfig);
    echo "AI configuration added to .env file successfully!\n";
} else {
    echo "AI configuration already exists in .env file.\n";
}

echo "\nPlease run: php artisan config:clear\n";
