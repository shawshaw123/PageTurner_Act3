<?php

namespace App\Integrations\Email;

use App\Models\NotificationLog;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Carbon;

/**
 * IP-4: records every email sent (or failed) in notification_logs, with how
 * long it waited between being queued and being sent. Registered in
 * AppServiceProvider. Report: php artisan mail:delivery-report
 */
class LogNotificationDelivery
{
    public function handleSent(NotificationSent $event): void
    {
        $this->log($event->notification, $event->notifiable, $event->channel, 'sent');
    }

    public function handleFailed(NotificationFailed $event): void
    {
        $error = $event->data['exception'] ?? null;
        $this->log($event->notification, $event->notifiable, $event->channel, 'failed', $error instanceof \Throwable ? $error->getMessage() : null);
    }

    private function log(object $notification, object $notifiable, string $channel, string $status, ?string $error = null): void
    {
        if ($channel !== 'mail') {
            return; // only emails are measured
        }

        try {
            $queuedAt = !empty($notification->queuedAt) ? Carbon::parse($notification->queuedAt) : null;
            $now = now();

            NotificationLog::create([
                'notification' => class_basename($notification),
                'channel' => $channel,
                'recipient' => $notifiable->email ?? null,
                'order_id' => method_exists($notification, 'deliveryOrderId') ? $notification->deliveryOrderId() : null,
                'status' => $status,
                'queued_at' => $queuedAt,
                'sent_at' => $status === 'sent' ? $now : null,
                'delivery_seconds' => $queuedAt && $status === 'sent' ? max(0, (int) round($queuedAt->diffInSeconds($now, true))) : null,
                'error' => $error ? mb_substr($error, 0, 1000) : null,
            ]);
        } catch (\Throwable $e) {
            report($e); // logging must never break sending
        }
    }
}
