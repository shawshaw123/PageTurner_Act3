<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIChatService
{
    protected AIServiceManager $aiManager;

    public function __construct(AIServiceManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Process a chat message and return AI response.
     */
    public function chat(string $message, string $sessionId, ?int $userId = null): array
    {
        // Get or create conversation
        $conversation = AiConversation::firstOrCreate(
            ['session_id' => $sessionId, 'status' => 'active'],
            ['user_id' => $userId, 'title' => $this->generateTitle($message)]
        );

        // Save user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $message,
        ]);

        // Get conversation history
        $history = $this->getHistory($conversation);

        // Check if user is asking about books - use RAG (Retrieval Augmented Generation)
        $context = $this->retrieveRelevantContext($message);

        // Build enhanced prompt with context
        $enhancedPrompt = $this->buildPrompt($message, $context);

        // Get AI response with fallback
        $result = $this->aiManager->generate($enhancedPrompt, $history, 'chat');

        // Save assistant message
        $aiMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $result['content'],
            'provider' => $result['provider'],
            'model' => $result['model'],
            'tokens_used' => $result['tokens_used'],
            'cost_estimate' => $result['cost_estimate'],
            'response_time' => $result['response_time'],
            'metadata' => [
                'context_used' => !empty($context),
                'books_found' => count($context['books'] ?? []),
            ],
        ]);

        // Log for audit
        Log::channel('single')->info('AI Chat Response', [
            'feature' => 'customer_support_chat',
            'conversation_id' => $conversation->id,
            'provider_used' => $result['provider'],
            'tokens' => $result['tokens_used'],
            'response_time' => $result['response_time'],
            'user_id' => $userId,
        ]);

        return [
            'message' => $result['content'],
            'conversation_id' => $conversation->id,
            'provider' => $result['provider'],
            'model' => $result['model'],
            'response_time' => $result['response_time'],
            'tokens_used' => $result['tokens_used'],
        ];
    }

    /**
     * Retrieve relevant book data for RAG (Retrieval Augmented Generation).
     */
    private function retrieveRelevantContext(string $message): array
    {
        $context = ['books' => [], 'categories' => []];
        $lowerMessage = strtolower($message);

        // Check if user is asking about specific books
        $searchTerms = $this->extractSearchTerms($message);

        if (!empty($searchTerms)) {
            $books = Book::where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->orWhere('title', 'LIKE', "%{$term}%")
                          ->orWhere('author', 'LIKE', "%{$term}%");
                }
            })
            ->where('is_active', true)
            ->select(['id', 'title', 'author', 'price', 'stock_quantity', 'isbn', 'description', 'category_id'])
            ->with('category:id,name')
            ->limit(5)
            ->get();

            $context['books'] = $books->map(function ($book) {
                return [
                    'title' => $book->title,
                    'author' => $book->author,
                    'price' => '$' . number_format($book->price, 2),
                    'in_stock' => $book->stock_quantity > 0 ? 'Yes (' . $book->stock_quantity . ' available)' : 'Out of stock',
                    'isbn' => $book->isbn,
                    'category' => $book->category->name ?? 'Uncategorized',
                    'description' => \Str::limit($book->description, 200),
                ];
            })->toArray();
        }

        // Check if asking about categories
        if (str_contains($lowerMessage, 'categor') || str_contains($lowerMessage, 'genre')) {
            $context['categories'] = DB::table('categories')
                ->select('name')
                ->limit(20)
                ->pluck('name')
                ->toArray();
        }

        // Check if asking about store stats
        if (str_contains($lowerMessage, 'how many') || str_contains($lowerMessage, 'total') || str_contains($lowerMessage, 'stock')) {
            $context['stats'] = [
                'total_books' => Book::where('is_active', true)->count(),
                'in_stock' => Book::where('is_active', true)->where('stock_quantity', '>', 0)->count(),
                'categories' => DB::table('categories')->count(),
                'price_range' => DB::table('books')
                    ->where('is_active', true)
                    ->selectRaw('MIN(price) as min_price, MAX(price) as max_price, AVG(price) as avg_price')
                    ->first(),
            ];
        }

        return $context;
    }

    /**
     * Build an enhanced prompt with retrieved context.
     */
    private function buildPrompt(string $message, array $context): string
    {
        $prompt = $message;

        if (!empty($context['books'])) {
            $bookInfo = "Here are relevant books from our catalog:\n";
            foreach ($context['books'] as $book) {
                $bookInfo .= "- \"{$book['title']}\" by {$book['author']} | Price: {$book['price']} | Stock: {$book['in_stock']} | Category: {$book['category']}\n";
                if (!empty($book['description'])) {
                    $bookInfo .= "  Description: {$book['description']}\n";
                }
            }
            $prompt = "Context from our book database:\n{$bookInfo}\n\nCustomer question: {$message}\n\nPlease answer using the book information provided above when relevant.";
        }

        if (!empty($context['categories'])) {
            $catList = implode(', ', $context['categories']);
            $prompt .= "\n\nAvailable categories in our store: {$catList}";
        }

        if (!empty($context['stats'])) {
            $stats = $context['stats'];
            $prompt .= "\n\nStore statistics: We have {$stats['total_books']} books in our catalog, {$stats['in_stock']} are in stock across {$stats['categories']} categories.";
            if ($stats['price_range']) {
                $prompt .= " Prices range from $" . number_format($stats['price_range']->min_price, 2) . " to $" . number_format($stats['price_range']->max_price, 2) . ".";
            }
        }

        return $prompt;
    }

    /**
     * Extract search terms from user message.
     */
    private function extractSearchTerms(string $message): array
    {
        $lowerMessage = strtolower($message);

        // Skip if it's a general question
        $generalPatterns = ['hello', 'hi', 'help', 'thank', 'bye', 'how are', 'what can', 'who are'];
        foreach ($generalPatterns as $pattern) {
            if (str_contains($lowerMessage, $pattern)) {
                return [];
            }
        }

        // Check if asking about books
        $bookPatterns = ['book', 'find', 'search', 'look', 'recommend', 'suggest', 'about', 'author', 'title', 'read'];
        $isBookQuery = false;
        foreach ($bookPatterns as $pattern) {
            if (str_contains($lowerMessage, $pattern)) {
                $isBookQuery = true;
                break;
            }
        }

        if (!$isBookQuery && !str_contains($lowerMessage, '?')) {
            return [];
        }

        // Extract meaningful words (3+ chars, no stop words)
        $stopWords = ['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'has', 'have', 'from', 'with', 'they', 'been', 'this', 'that', 'what', 'book', 'books', 'find', 'search', 'look', 'looking', 'want', 'need', 'about', 'any', 'some', 'please', 'could', 'would', 'like', 'recommend', 'suggest', 'show', 'tell'];

        $words = preg_split('/\s+/', strtolower(preg_replace('/[^\w\s]/', '', $message)));
        $terms = array_filter($words, fn($w) => strlen($w) >= 3 && !in_array($w, $stopWords));

        return array_values(array_slice($terms, 0, 3));
    }

    /**
     * Get conversation history for context.
     */
    private function getHistory(AiConversation $conversation): array
    {
        $maxHistory = config('ai.chat.max_history', 20);

        return $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit($maxHistory)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();
    }

    /**
     * Generate a conversation title from the first message.
     */
    private function generateTitle(string $message): string
    {
        return \Str::limit($message, 50);
    }

    /**
     * Get conversation history for display.
     */
    public function getConversationMessages(string $sessionId): array
    {
        $conversation = AiConversation::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$conversation) {
            return [];
        }

        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'provider' => $m->provider,
                'timestamp' => $m->created_at->format('g:i A'),
            ])
            ->toArray();
    }

    /**
     * Start a new conversation by closing the current one.
     */
    public function newConversation(string $sessionId): void
    {
        AiConversation::where('session_id', $sessionId)
            ->where('status', 'active')
            ->update(['status' => 'closed']);
    }
}
