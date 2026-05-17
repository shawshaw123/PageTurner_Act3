<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;

class AIServiceManager
{
    /**
     * Generate a response using the default provider with automatic fallback.
     */
    public function generate(string $prompt, array $messages = [], string $feature = 'chat'): array
    {
        if (config('ai.fallback_enabled')) {
            return $this->generateWithFallback($prompt, $messages, $feature);
        }

        $provider = config('ai.default_provider', 'openai');
        return $this->callProvider($provider, $prompt, $messages, $feature);
    }

    /**
     * Try each provider in the fallback chain until one succeeds.
     */
    public function generateWithFallback(string $prompt, array $messages = [], string $feature = 'chat'): array
    {
        $chain = config('ai.fallback_chain', ['openai', 'gemini', 'ollama']);

        foreach ($chain as $provider) {
            try {
                if (!$this->isAvailable($provider)) {
                    Log::info("AI Provider {$provider} is not available, skipping.");
                    continue;
                }

                $result = $this->callProvider($provider, $prompt, $messages, $feature);

                Log::channel('single')->info('AI Response Success', [
                    'provider' => $provider,
                    'feature' => $feature,
                ]);

                return $result;

            } catch (\Exception $e) {
                Log::warning("AI Provider {$provider} failed: " . $e->getMessage());
                continue;
            }
        }

        // All providers failed - return graceful error
        return [
            'content' => 'I apologize, but I am temporarily unable to process your request. Please try again in a moment.',
            'provider' => 'none',
            'model' => 'none',
            'tokens_used' => 0,
            'cost_estimate' => 0,
            'response_time' => 0,
        ];
    }

    /**
     * Call a specific AI provider.
     */
    public function callProvider(string $provider, string $prompt, array $messages = [], string $feature = 'chat'): array
    {
        $startTime = microtime(true);

        $result = match ($provider) {
            'openai' => $this->callOpenAI($prompt, $messages),
            'gemini' => $this->callGemini($prompt, $messages),
            'ollama' => $this->callOllama($prompt, $messages),
            'mock' => $this->callMock($prompt, $messages),
            default => throw new \RuntimeException("Unknown AI provider: {$provider}"),
        };

        $result['response_time'] = round(microtime(true) - $startTime, 3);
        $result['provider'] = $provider;

        // Log usage
        $this->logUsage($provider, $feature, $result);

        return $result;
    }

    /**
     * Call OpenAI GPT-4o-mini API.
     */
    private function callOpenAI(string $prompt, array $messages = []): array
    {
        $apiKey = config('ai.providers.openai.api_key');
        $model = config('ai.providers.openai.model', 'gpt-4o-mini');
        $maxTokens = config('ai.providers.openai.max_tokens', 1024);

        $guzzleClient = new \GuzzleHttp\Client([
            'verify' => config('app.env') === 'local' ? false : true,
        ]);

        $client = OpenAI::factory()
            ->withHttpClient($guzzleClient)
            ->withApiKey($apiKey)
            ->make();

        // Build message array
        $chatMessages = [];

        // Add system prompt
        $chatMessages[] = [
            'role' => 'system',
            'content' => config('ai.chat.system_prompt'),
        ];

        // Add conversation history
        foreach ($messages as $msg) {
            $chatMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // Add current user message
        $chatMessages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $response = $client->chat()->create([
            'model' => $model,
            'messages' => $chatMessages,
            'max_tokens' => $maxTokens,
            'temperature' => config('ai.providers.openai.temperature', 0.7),
        ]);

        $usage = $response->usage;

        return [
            'content' => $response->choices[0]->message->content,
            'model' => $model,
            'prompt_tokens' => $usage->promptTokens ?? 0,
            'completion_tokens' => $usage->completionTokens ?? 0,
            'tokens_used' => $usage->totalTokens ?? 0,
            'cost_estimate' => $this->calculateCost('openai', $usage->totalTokens ?? 0),
        ];
    }

    /**
     * Call Google Gemini API.
     */
    private function callGemini(string $prompt, array $messages = []): array
    {
        $apiKey = config('ai.providers.gemini.api_key');
        $model = config('ai.providers.gemini.model', 'gemini-1.5-flash');

        if (!$apiKey) {
            throw new \RuntimeException('Gemini API key not configured');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Build contents
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $http = Http::timeout(30);
        if (config('app.env') === 'local') {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($url, [
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => config('ai.chat.system_prompt')]],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API error: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';
        $tokens = $data['usageMetadata']['totalTokenCount'] ?? 0;

        return [
            'content' => $text,
            'model' => $model,
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
            'tokens_used' => $tokens,
            'cost_estimate' => 0, // Gemini free tier
        ];
    }

    /**
     * Call local Ollama API.
     */
    private function callOllama(string $prompt, array $messages = []): array
    {
        $baseUrl = config('ai.providers.ollama.base_url', 'http://localhost:11434');
        $model = config('ai.providers.ollama.model', 'llama3.2');

        $chatMessages = [
            ['role' => 'system', 'content' => config('ai.chat.system_prompt')],
        ];

        foreach ($messages as $msg) {
            $chatMessages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $chatMessages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::timeout(60)->post("{$baseUrl}/api/chat", [
            'model' => $model,
            'messages' => $chatMessages,
            'stream' => false,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Ollama error: ' . $response->body());
        }

        $data = $response->json();

        return [
            'content' => $data['message']['content'] ?? 'No response.',
            'model' => $model,
            'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
            'completion_tokens' => $data['eval_count'] ?? 0,
            'tokens_used' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
            'cost_estimate' => 0, // Local, free
        ];
    }

    /**
     * Call mock AI provider for testing without API keys.
     */
    private function callMock(string $prompt, array $messages = []): array
    {
        $lowerPrompt = strtolower($prompt);

        // Generate contextual responses based on the user's question
        $response = $this->generateMockResponse($lowerPrompt);

        return [
            'content' => $response,
            'model' => 'mock-gpt-4o-mini',
            'prompt_tokens' => strlen($prompt) / 4, // Rough estimate
            'completion_tokens' => strlen($response) / 4,
            'tokens_used' => (strlen($prompt) + strlen($response)) / 4,
            'cost_estimate' => 0,
        ];
    }

    /**
     * Generate a mock response based on the user's query.
     */
    private function generateMockResponse(string $prompt): string
    {
        // Check for common queries and provide relevant responses
        if (str_contains($prompt, 'hello') || str_contains($prompt, 'hi') || str_contains($prompt, 'hey')) {
            return "Hello! I'm your PageTurner AI Assistant. How can I help you find books today?";
        }

        if (str_contains($prompt, 'book') || str_contains($prompt, 'recommend') || str_contains($prompt, 'suggest')) {
            return "I'd be happy to help you find books! Based on our catalog, we have a great selection of fiction, non-fiction, programming, and many other genres. Could you tell me more about what type of books you're interested in? For example:\n\n- Fiction novels\n- Programming and tech books\n- Self-help and business\n- Children's books\n- And many more!";
        }

        if (str_contains($prompt, 'available') || str_contains($prompt, 'stock') || str_contains($prompt, 'inventory')) {
            return "I can help you check our inventory! Our bookstore currently has a wide selection of books across multiple categories. To get specific information about availability, please let me know:\n\n- Which book or author you're looking for\n- The genre or category you're interested in\n- Your budget range\n\nI'll then search our catalog and provide you with up-to-date stock information!";
        }

        if (str_contains($prompt, 'price') || str_contains($prompt, 'cost') || str_contains($prompt, 'budget') || str_contains($prompt, 'under')) {
            return "We have books available across various price ranges to fit every budget! Our catalog includes:\n\n- Budget-friendly options under $15\n- Mid-range books between $15-$30\n- Premium editions and special collections\n\nTo find books within your specific budget, just let me know your price range and I'll search for matching titles!";
        }

        if (str_contains($prompt, 'author')) {
            return "We have books from many talented authors! To help you find books by a specific author or discover new writers, please tell me:\n\n- The author's name you're looking for\n- Or the genre/subject you're interested in\n\nI'll search our catalog and show you relevant books with author information!";
        }

        if (str_contains($prompt, 'help') || str_contains($prompt, 'what can')) {
            return "I'm here to help you with all your bookstore needs! Here's what I can do:\n\n📚 **Find Books**: Search for specific titles, authors, or genres\n💡 **Get Recommendations**: Receive personalized book suggestions\n📦 **Check Inventory**: Verify stock availability and pricing\n🔍 **Answer Questions**: Provide information about our bookstore and services\n\nJust ask me anything about books, and I'll do my best to assist you!";
        }

        // Default response
        return "Thank you for your question! I'm your PageTurner AI Assistant and I'm here to help you with book recommendations, inventory checks, and any questions about our bookstore. \n\nTo get started, you can ask me about:\n- Book recommendations by genre\n- Specific authors or titles\n- Stock availability\n- Price ranges and budget options\n\nWhat would you like to know?";
    }

    /**
     * Check if a provider is available.
     */
    public function isAvailable(string $provider): bool
    {
        return match ($provider) {
            'openai' => !empty(config('ai.providers.openai.api_key')),
            'gemini' => !empty(config('ai.providers.gemini.api_key')),
            'ollama' => config('ai.providers.ollama.enabled', false),
            'mock' => true, // Mock provider always available for testing
            default => false,
        };
    }

    /**
     * Calculate estimated cost for a provider.
     */
    private function calculateCost(string $provider, int $tokens): float
    {
        // GPT-4o-mini: ~$0.15/1M input, ~$0.60/1M output
        return match ($provider) {
            'openai' => ($tokens / 1_000_000) * 0.30,
            default => 0,
        };
    }

    /**
     * Log AI usage for cost tracking and audit.
     */
    private function logUsage(string $provider, string $feature, array $result): void
    {
        try {
            AiUsageLog::create([
                'provider' => $provider,
                'feature' => $feature,
                'model' => $result['model'] ?? null,
                'prompt_tokens' => $result['prompt_tokens'] ?? 0,
                'completion_tokens' => $result['completion_tokens'] ?? 0,
                'total_tokens' => $result['tokens_used'] ?? 0,
                'cost_estimate' => $result['cost_estimate'] ?? 0,
                'response_time' => $result['response_time'] ?? null,
                'success' => true,
                'user_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI usage: ' . $e->getMessage());
        }
    }

    /**
     * Get usage statistics for the AI dashboard.
     */
    public function getUsageStats(): array
    {
        $today = now()->startOfDay();

        return [
            'today_tokens' => AiUsageLog::where('created_at', '>=', $today)->sum('total_tokens'),
            'today_cost' => AiUsageLog::where('created_at', '>=', $today)->sum('cost_estimate'),
            'today_requests' => AiUsageLog::where('created_at', '>=', $today)->count(),
            'total_tokens' => AiUsageLog::sum('total_tokens'),
            'total_cost' => AiUsageLog::sum('cost_estimate'),
            'total_requests' => AiUsageLog::count(),
            'by_provider' => AiUsageLog::selectRaw('provider, COUNT(*) as count, SUM(total_tokens) as tokens')
                ->groupBy('provider')->get()->toArray(),
        ];
    }
}
