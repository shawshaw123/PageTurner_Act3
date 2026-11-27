<?php

namespace App\Notifications;

use App\Integrations\Email\TracksDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** IP-4: sent to the buyer and the seller when PayMongo confirms payment (IP-3). */
class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable, TracksDelivery;

    protected $order;

    public function __construct($order)
    {
        $this->markQueued();
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isBuyer = $notifiable->id === $this->order->user_id;
        $amount = '₱' . number_format($this->order->total_amount, 2);

        $mail = (new MailMessage)
            ->subject('Payment received - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!');

        return $isBuyer
            ? $mail->line("We received your payment of {$amount} for order #{$this->order->id}.")
                ->line('The seller will now prepare your books.')
                ->action('View Order', url('/orders/' . $this->order->id))
            : $mail->line("Order #{$this->order->id} ({$amount}) has been paid online.")
                ->line('You can now process and ship it.')
                ->action('View Order', url('/orders/' . $this->order->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_paid',
            'order_id' => $this->order->id,
            'total_amount' => $this->order->total_amount,
            'message' => 'Order #' . $this->order->id . ' has been paid.',
        ];
    }
}
