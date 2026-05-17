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

        // Fiction/Novel queries
        if (str_contains($prompt, 'fiction') || str_contains($prompt, 'novel')) {
            $books = \App\Models\Book::where('title', 'like', '%fiction%')
                ->orWhere('description', 'like', '%novel%')
                ->orWhereIn('category_id', function($q) {
                    $q->select('id')->from('categories')->where('name', 'like', '%fiction%');
                })
                ->limit(3)
                ->get(['title', 'author', 'price']);

            if ($books->count() > 0) {
                $response = "Here are some great fiction novels from our catalog:\n\n";
                foreach ($books as $book) {
                    $response .= "📖 **{$book->title}** by {$book->author} - \${$book->price}\n";
                }
                $response .= "\nWould you like more details about any of these books?";
                return $response;
            }
            return "We have a wonderful selection of fiction novels! Our catalog includes contemporary fiction, classics, thrillers, romance, and more. Prices range from $9.99 to $49.99. What genre of fiction interests you most?";
        }

        // Programming queries
        if (str_contains($prompt, 'programming') || str_contains($prompt, 'tech') || str_contains($prompt, 'code')) {
            $books = \App\Models\Book::where('title', 'like', '%programming%')
                ->orWhere('title', 'like', '%code%')
                ->orWhere('description', 'like', '%programming%')
                ->limit(3)
                ->get(['title', 'author', 'price']);

            if ($books->count() > 0) {
                $response = "Here are some popular programming books:\n\n";
                foreach ($books as $book) {
                    $response .= "💻 **{$book->title}** by {$book->author} - \${$book->price}\n";
                }
                $response .= "\nThese are great for learning new skills!";
                return $response;
            }
            return "We have excellent programming books covering Python, JavaScript, Java, web development, and more. Most are priced between $19.99 and $39.99. Which programming language are you interested in?";
        }

        // Price range queries
        if (str_contains($prompt, 'price') || str_contains($prompt, 'cost') || str_contains($prompt, 'budget')) {
            if (str_contains($prompt, 'under') || str_contains($prompt, 'cheap') || str_contains($prompt, 'budget')) {
                $books = \App\Models\Book::where('price', '<', 15)->limit(3)->get(['title', 'author', 'price']);
                if ($books->count() > 0) {
                    $response = "Here are budget-friendly books under $15:\n\n";
                    foreach ($books as $book) {
                        $response .= "💰 **{$book->title}** by {$book->author} - \${$book->price}\n";
                    }
                    return $response;
                }
            }
            if (str_contains($prompt, 'mid') || str_contains($prompt, '15') || str_contains($prompt, '30')) {
                $books = \App\Models\Book::whereBetween('price', [15, 30])->limit(3)->get(['title', 'author', 'price']);
                if ($books->count() > 0) {
                    $response = "Here are mid-range books ($15-$30):\n\n";
                    foreach ($books as $book) {
                        $response .= "📚 **{$book->title}** by {$book->author} - \${$book->price}\n";
                    }
                    return $response;
                }
            }
            return "Our books range from $9.99 to $49.99:\n\n- Under $15: Great for budget readers\n- $15-$30: Mid-range with good value\n- Over $30: Premium editions and special collections\n\nWhat's your budget?";
        }

        // List of books query
        if (str_contains($prompt, 'list') || str_contains($prompt, 'what books') || str_contains($prompt, 'show me')) {
            $books = \App\Models\Book::limit(5)->get(['title', 'author', 'price', 'stock_quantity']);
            $response = "Here are some books from our catalog:\n\n";
            foreach ($books as $book) {
                $stock = $book->stock_quantity > 0 ? "✅ In Stock ({$book->stock_quantity})" : "❌ Out of Stock";
                $response .= "📖 **{$book->title}** by {$book->author}\n   Price: \${$book->price} | $stock\n\n";
            }
            $response .= "We have many more books! Would you like to search by genre or author?";
            return $response;
        }

        // Stock/Inventory queries
        if (str_contains($prompt, 'stock') || str_contains($prompt, 'available') || str_contains($prompt, 'inventory')) {
            $totalBooks = \App\Models\Book::count();
            $inStock = \App\Models\Book::where('stock_quantity', '>', 0)->count();
            return "📦 **Inventory Status:**\n\n- Total books in catalog: {$totalBooks}\n- Currently in stock: {$inStock}\n- Out of stock: " . ($totalBooks - $inStock) . "\n\nI can check specific book availability if you tell me which title you're interested in!";
        }

        // Author queries
        if (str_contains($prompt, 'author')) {
            $authors = \App\Models\Book::select('author')->distinct()->limit(5)->pluck('author');
            $response = "We have books by many talented authors! Here are some featured authors:\n\n";
            foreach ($authors as $author) {
                $response .= "✍️ {$author}\n";
            }
            $response .= "\nWhich author are you interested in?";
            return $response;
        }

        // Help queries
        if (str_contains($prompt, 'help') || str_contains($prompt, 'what can')) {
            return "I'm here to help! Here's what I can do:\n\n📚 **Find Books** - Search by title, author, or genre\n� **Price Search** - Find books within your budget\n📦 **Check Stock** - Verify availability\n� **Get Recommendations** - Personalized suggestions\n\nTry asking: \"Show me fiction books under $20\" or \"What programming books do you have?\"";
        }

        // Generic book query
        if (str_contains($prompt, 'book') || str_contains($prompt, 'recommend')) {
            $randomBook = \App\Models\Book::inRandomOrder()->first(['title', 'author', 'price', 'description']);
            if ($randomBook) {
                return "📖 **Featured Book Recommendation:**\n\n**{$randomBook->title}** by {$randomBook->author}\nPrice: \${$randomBook->price}\n\n{$randomBook->description}\n\nWould you like more recommendations?";
            }
        }

        // Default response
        return "I'd be happy to help! You can ask me about:\n\n- Book recommendations by genre\n- Specific authors or titles\n- Stock availability\n- Price ranges\n- Programming books\n- Fiction novels\n\nWhat would you like to know?";
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
