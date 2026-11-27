<?php

namespace App\Integrations\Email;

/**
 * IP-4: add to a queued notification so LogNotificationDelivery can record how
 * long the email waited in the queue before it was sent (Objective 4: within 60 s).
 * Call markQueued() in the notification's constructor.
 */
trait TracksDelivery
{
    /** When the notification was created and queued (ISO 8601). */
    public ?string $queuedAt = null;

    protected function markQueued(): void
    {
        $this->queuedAt = now()->toIso8601String();
    }

    /** The order this email is about, if any (for the delivery log). */
    public function deliveryOrderId(): ?int
    {
        return isset($this->order) ? ($this->order->id ?? null) : null;
    }
}
