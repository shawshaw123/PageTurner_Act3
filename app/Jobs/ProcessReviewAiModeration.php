<?php

namespace App\Jobs;

use App\Models\Review;
use App\Services\AIServiceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReviewAiModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Review $review;

    /**
     * Create a new job instance.
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    /**
     * Execute the job.
     */
    public function handle(AIServiceManager $aiManager): void
    {
        Log::info("Starting background AI review moderation for review ID: {$this->review->id}");

        // Build prompt for AI moderation
        $prompt = "You are an automated AI content moderator for PageTurner bookstore. 
Analyze this customer review and respond with a raw JSON object.

Review Comment: \"{$this->review->comment}\"
Review Rating: {$this->review->rating} stars

Your output must be EXACTLY a JSON block (no markdown, no backticks, no wrap, just the raw JSON string) with these keys and nothing else:
{
    \"status\": \"approved\" or \"rejected\",
    \"sentiment\": \"positive\", \"negative\", or \"neutral\",
    \"summary\": \"A short 1-sentence summary of the user's feedback.\",
    \"reason\": \"A brief explanation of your moderation decision.\"
}

Rules for rejecting:
- Contains severe profanity, hate speech, threats, or harassment.
- Is obvious spam, gibberish, or advertisement for external sites.
Otherwise, approve it even if it is a negative review of the book (critical opinions are welcome!).";

        try {
            // Run using AI service manager fallback chain
            $result = $aiManager->generate($prompt, [], 'content_generation');
            $content = trim($result['content']);

            // Strip markdown block helper if present
            if (str_starts_with($content, '```')) {
                $content = preg_replace('/^```(?:json)?\n?|```$/i', '', $content);
                $content = trim($content);
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['status'])) {
                // If it fails to parse JSON, fallback to standard rule-based parsing or manual check
                Log::warning("AI did not return valid JSON for moderation. Raw content: {$content}");
                
                // Graceful fallback parsing
                $status = str_contains(strtolower($content), 'rejected') ? 'rejected' : 'approved';
                $data = [
                    'status' => $status,
                    'sentiment' => 'neutral',
                    'summary' => \Str::limit($this->review->comment, 60),
                    'reason' => 'Bypassed due to parsing error.'
                ];
            }

            // Update Review Status
            $this->review->update([
                'status' => $data['status']
            ]);

            // Save decision to AI audit log
            Log::channel('single')->info('AI Review Moderation Decision', [
                'feature' => 'content_moderation',
                'review_id' => $this->review->id,
                'comment' => $this->review->comment,
                'status_decision' => $data['status'],
                'sentiment' => $data['sentiment'],
                'summary' => $data['summary'],
                'reason' => $data['reason'],
                'provider' => $result['provider'] ?? 'none',
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to run AI review moderation: " . $e->getMessage());
            
            // Graceful fallback: Approve if AI breaks so that the user experience is not impacted
            $this->review->update([
                'status' => 'approved'
            ]);
        }
    }
}
