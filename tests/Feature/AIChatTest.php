<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Jobs\ProcessReviewAiModeration;
use App\Services\AIServiceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AIChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Book $book;
    protected $mockAiManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $this->user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Create admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create category and book
        $category = \App\Models\Category::create(['name' => 'Fiction']);
        $this->book = Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'price' => 14.99,
            'stock_quantity' => 10,
            'isbn' => '9780743273565',
            'description' => 'A classic novel of the Jazz Age.',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        // Mock AIServiceManager globally to prevent external API calls during testing
        $this->mockAiManager = $this->createMock(AIServiceManager::class);
        $this->mockAiManager->method('generate')->willReturn([
            'content' => 'This is a mock AI response.',
            'provider' => 'mock',
            'model' => 'mock-gpt-4o-mini',
            'tokens_used' => 20,
            'cost_estimate' => 0.000006,
            'response_time' => 0.123,
        ]);
        
        $this->mockAiManager->method('getUsageStats')->willReturn([
            'today_tokens' => 100,
            'today_cost' => 0.05,
            'today_requests' => 5,
            'total_tokens' => 1000,
            'total_cost' => 0.50,
            'total_requests' => 50,
            'by_provider' => [['provider' => 'mock', 'count' => 50, 'tokens' => 1000]],
        ]);

        $this->app->instance(AIServiceManager::class, $this->mockAiManager);
    }

    /**
     * Test chat page accessibility.
     */
    public function test_chat_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('ai.chat'));
        $response->assertStatus(200);
        $response->assertSee('PageTurner AI');
    }

    /**
     * Test sending a support message.
     */
    public function test_sending_message_creates_conversation_and_returns_response(): void
    {
        $sessionId = 'test-session-123';
        
        $response = $this->actingAs($this->user)->postJson(route('ai.chat.send'), [
            'message' => 'Hello AI, recommend some books',
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'message',
                    'conversation_id',
                    'provider',
                    'model',
                    'response_time',
                    'tokens_used'
                ]
            ]);

        // Verify conversation and messages are in the DB
        $this->assertDatabaseHas('ai_conversations', [
            'session_id' => $sessionId,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'role' => 'user',
            'content' => 'Hello AI, recommend some books',
        ]);
    }

    /**
     * Test getting conversation history.
     */
    public function test_getting_chat_history(): void
    {
        $sessionId = 'test-history-456';
        $conversation = AiConversation::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'title' => 'Test History',
            'user_id' => $this->user->id,
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hello! How can I help you?',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('ai.chat.history', ['session_id' => $sessionId]));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.content', 'Hello')
            ->assertJsonPath('messages.1.content', 'Hello! How can I help you?');
    }

    /**
     * Test starting a new conversation.
     */
    public function test_starting_new_conversation_closes_old_one(): void
    {
        $sessionId = 'test-new-conversation-789';
        
        $conversation = AiConversation::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'title' => 'Active Chat',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.chat.new'), [
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(200);

        // Verify old conversation is closed
        $this->assertDatabaseHas('ai_conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }

    /**
     * Test admin AI dashboard restriction.
     */
    public function test_non_admin_cannot_access_ai_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.ai.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_ai_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ai.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('AI Usage & Monitoring');
    }

    /**
     * Test queue job for review moderation.
     */
    public function test_review_creation_dispatches_ai_moderation_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => 'This is a fantastic book! Highly recommended.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert job was pushed to queue
        Queue::assertPushed(ProcessReviewAiModeration::class);

        // Assert review was created as pending
        $this->assertDatabaseHas('reviews', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'comment' => 'This is a fantastic book! Highly recommended.',
            'status' => 'pending',
        ]);
    }

    /**
     * Test that ProcessReviewAiModeration job moderates successfully.
     */
    public function test_review_moderation_job_executes_successfully(): void
    {
        $review = Review::create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'comment' => 'Great book!',
            'status' => 'pending',
        ]);

        // Mock AI Manager specifically for moderation response
        $mockAi = $this->createMock(AIServiceManager::class);
        $mockAi->expects($this->once())
            ->method('generate')
            ->willReturn([
                'content' => json_encode([
                    'status' => 'approved',
                    'sentiment' => 'positive',
                    'summary' => 'The user highly praises the book.',
                    'reason' => 'No offensive language found.'
                ]),
                'provider' => 'mock',
            ]);

        $job = new ProcessReviewAiModeration($review);
        $job->handle($mockAi);

        // Verify review is now approved
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }
}
