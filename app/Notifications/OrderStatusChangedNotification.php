<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $oldStatus;

    public function __construct($order, $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #' . $this->order->id . ' Status Updated')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order #' . $this->order->id . ' status has been updated.')
            ->line('Previous Status: ' . ucfirst($this->oldStatus))
            ->line('New Status: ' . ucfirst($this->order->status))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for shopping with PageTurner!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->order->status,
            'message' => 'Order #' . $this->order->id . ' status changed to ' . ucfirst($this->order->status),
        ];
    }
}
