<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Confirmed - #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order #' . $this->order->id . ' has been placed successfully.')
            ->line('Total Amount: ₱' . number_format($this->order->total_amount, 2))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for shopping with PageTurner!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'total_amount' => $this->order->total_amount,
            'message' => 'Your order #' . $this->order->id . ' has been placed.',
        ];
    }
}
