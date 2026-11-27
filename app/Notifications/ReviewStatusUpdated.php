<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ReviewStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $review;
    public $status;

    public function __construct(Review $review, $status)
    {
        $this->review = $review;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusText = $this->getStatusText();
        $statusColor = $this->getStatusColor();
        
        return (new MailMessage)
            ->subject('Review Status Update: ' . $this->review->book->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your review for the book "' . $this->review->book->title . '" has been ' . $statusText . '.')
            ->line('Review Details:')
            ->line('• Your Rating: ' . $this->review->rating . ' stars')
            ->line('• Your Comment: ' . ($this->review->comment ?: 'No comment provided'))
            ->line('• Status: <span style="color: ' . $statusColor . '; font-weight: bold;">' . $statusText . '</span>')
            ->action('View Your Review', route('books.show', $this->review->book))
            ->line('Thank you for contributing to the PageTurner Bookstore!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'book_id' => $this->review->book_id,
            'rating' => $this->review->rating,
            'comment' => $this->review->comment,
            'book_title' => $this->review->book->title,
            'status' => $this->status,
            'message' => 'Your review for "' . $this->review->book->title . '" has been ' . $this->getStatusText(),
            'type' => 'review_status',
            'created_at' => now(),
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'book_id' => $this->review->book_id,
            'status' => $this->status,
            'message' => 'Your review status has been updated',
        ];
    }

    private function getStatusText()
    {
        switch ($this->status) {
            case 'approved':
                return 'approved';
            case 'rejected':
                return 'rejected';
            default:
                return 'updated';
        }
    }

    private function getStatusColor()
    {
        switch ($this->status) {
            case 'approved':
                return '#10b981'; // green
            case 'rejected':
                return '#ef4444'; // red
            default:
                return '#6b7280'; // gray
        }
    }
}
