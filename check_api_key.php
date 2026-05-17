<?php
$envFile = __DIR__ . '/.env';
$content = file_get_contents($envFile);

// Check for AI keys
if (str_contains($content, 'OPENAI_API_KEY=')) {
    preg_match('/OPENAI_API_KEY=(.*)/', $content, $matches);
    $key = $matches[1] ?? '';
    $result = "OPENAI_API_KEY found: " . (empty($key) ? "EMPTY" : "SET (length: " . strlen(trim($key)) . ")");
} else {
    $result = "OPENAI_API_KEY NOT FOUND in .env";
}

file_put_contents(__DIR__ . '/api_check_result.txt', $result);
echo $result;
