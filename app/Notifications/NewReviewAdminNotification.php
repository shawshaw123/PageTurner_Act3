<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewAdminNotification extends Notification
{
    use Queueable;

    protected $review;
    protected $book;

    public function __construct($review, $book)
    {
        $this->review = $review;
        $this->book = $book;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Review Submitted')
            ->greeting('Hello Admin!')
            ->line('A new review has been submitted for "' . $this->book->title . '".')
            ->line('Rating: ' . $this->review->rating . '/5')
            ->line('By: ' . $this->review->user->name)
            ->action('View Book', url('/books/' . $this->book->id))
            ->line('Please review this submission.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_review',
            'review_id' => $this->review->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'reviewer' => $this->review->user->name,
            'rating' => $this->review->rating,
            'message' => 'New review for "' . $this->book->title . '" by ' . $this->review->user->name,
        ];
    }
}
