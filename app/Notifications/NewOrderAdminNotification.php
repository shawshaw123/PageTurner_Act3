<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification
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
            ->subject('New Order Received - #' . $this->order->id)
            ->greeting('Hello Admin!')
            ->line('A new order has been placed by ' . $this->order->user->name . '.')
            ->line('Order #' . $this->order->id)
            ->line('Total Amount: ₱' . number_format($this->order->total_amount, 2))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Please review and process the order.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'customer_name' => $this->order->user->name,
            'total_amount' => $this->order->total_amount,
            'message' => 'New order #' . $this->order->id . ' from ' . $this->order->user->name,
        ];
    }
}
