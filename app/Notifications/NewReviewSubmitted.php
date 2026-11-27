<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewReviewSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Review Submitted: ' . $this->review->book->title)
            ->greeting('Hello ' . $notifiablename . ',')
            ->line('A new review has been submitted for the book "' . $this->review->book->title . '"')
            ->line('Review Details:')
            ->line('• Reviewer: ' . $this->review->user->name)
            ->line('• Rating: ' . $this->review->rating . ' stars')
            ->line('• Comment: ' . ($this->review->comment ?: 'No comment provided'))
            ->action('View Review', route('books.show', $this->review->book))
            ->line('Thank you for managing the PageTurner Bookstore!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'book_id' => $this->review->book_id,
            'user_id' => $this->review->user_id,
            'rating' => $this->review->rating,
            'comment' => $this->review->comment,
            'reviewer_name' => $this->review->user->name,
            'book_title' => $this->review->book->title,
            'message' => 'New review submitted for "' . $this->review->book->title . '"',
            'type' => 'new_review',
            'created_at' => now(),
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'book_id' => $this->review->book_id,
            'user_id' => $this->review->user_id,
            'rating' => $this->review->rating,
            'message' => 'New review submitted for "' . $this->review->book->title . '"',
        ];
    }
}
