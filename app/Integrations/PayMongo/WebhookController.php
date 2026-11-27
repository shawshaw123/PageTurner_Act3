<?php

namespace App\Integrations\PayMongo;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * IP-3: POST /webhooks/paymongo
 *
 * PayMongo calls this when a payment succeeds or fails. The signature header is
 * checked first; every event is stored in webhook_events and handled once.
 * Answers 2xx quickly (PayMongo retries otherwise).
 *
 *   checkout_session.payment.paid  -> orders paid
 *   payment.paid                   -> orders paid (same result; whichever arrives first)
 *   payment.failed                 -> payment failed (buyer may retry until the window closes)
 *   anything else                  -> stored and ignored
 *
 * PayMongo sends no event when a session expires; the scheduler handles expiry
 * (payments:expire-unpaid).
 */
class WebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $raw = $request->getContent();
        $secret = config('services.paymongo.webhook_secret');

        if (!WebhookSignature::isValid($raw, $request->header('Paymongo-Signature'), $secret)) {
            $this->store(null, null, false, 'rejected', null, $secret ? 'Invalid signature' : 'PAYMONGO_WEBHOOK_SECRET is not set');
            Log::warning('PayMongo webhook rejected', ['reason' => $secret ? 'invalid signature' : 'no webhook secret configured', 'ip' => $request->ip()]);

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($raw, true);
        $eventId = data_get($event, 'data.id');
        $type = data_get($event, 'data.attributes.type');
        $resource = (array) data_get($event, 'data.attributes.data', []);

        if (!$eventId || !$type) {
            return response()->json(['message' => 'Not a PayMongo event.'], 422);
        }

        // Each event is handled once, even if PayMongo delivers it again.
        $existing = WebhookEvent::where('event_id', $eventId)->first();
        if ($existing && in_array($existing->status, ['processed', 'ignored'], true)) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        try {
            $record = $existing ?? $this->store($eventId, $type, true, 'received', $raw);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['received' => true, 'duplicate' => true]); // a parallel delivery got here first
        }

        try {
            [$status, $note] = $this->handle($type, $resource, $payments);
            $record->update(['status' => $status, 'error' => $note, 'processed_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
            $record->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000)]);

            return response()->json(['message' => 'Processing failed; please retry.'], 500);
        }

        return response()->json(['received' => true, 'status' => $status]);
    }

    /** @return array{0: string, 1: ?string}  [status, note] */
    private function handle(string $type, array $resource, PaymentService $payments): array
    {
        $attributes = (array) ($resource['attributes'] ?? []);

        switch ($type) {
            case 'checkout_session.payment.paid':
                $payment = $payments->findPayment($resource['id'] ?? null, data_get($attributes, 'payment_intent.id'), $attributes['reference_number'] ?? data_get($attributes, 'metadata.checkout_reference'));
                if (!$payment) {
                    return ['ignored', 'No matching payment for checkout session ' . ($resource['id'] ?? '?')];
                }
                $paid = collect($attributes['payments'] ?? [])->first(fn ($p) => data_get($p, 'attributes.status') === 'paid');
                $payments->markPaid($payment, $paid['id'] ?? null);

                return ['processed', null];

            case 'payment.paid':
            case 'payment.failed':
                $payment = $payments->findPayment($attributes['checkout_session_id'] ?? null, $attributes['payment_intent_id'] ?? null, data_get($attributes, 'metadata.checkout_reference'));
                if (!$payment) {
                    return ['ignored', 'No matching payment for ' . ($resource['id'] ?? '?')];
                }
                if ($type === 'payment.paid') {
                    $payments->markPaid($payment, $resource['id'] ?? null);
                } else {
                    $payments->markFailed($payment, $attributes['failed_message'] ?? $attributes['failed_code'] ?? 'Payment failed');
                }

                return ['processed', null];

            default:
                return ['ignored', "Event type {$type} is not used"];
        }
    }

    private function store(?string $eventId, ?string $type, bool $valid, string $status, ?string $payload, ?string $error = null): WebhookEvent
    {
        return WebhookEvent::create([
            'provider' => 'paymongo',
            'event_id' => $eventId,
            'type' => $type,
            'signature_valid' => $valid,
            'status' => $status,
            // Only verified bodies are kept; a rejected call is recorded without its content.
            'payload' => $valid ? $payload : null,
            'error' => $error,
            'received_at' => now(),
        ]);
    }
}
