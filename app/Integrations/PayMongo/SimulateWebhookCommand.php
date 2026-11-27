<?php

namespace App\Integrations\PayMongo;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Local testing only: send this app a signed PayMongo webhook for a payment,
 * exactly as PayMongo would. Useful when PayMongo cannot reach your computer
 * (no public HTTPS URL). Needs PAYMONGO_WEBHOOK_SECRET in .env.
 *
 *   php artisan paymongo:simulate CHK-ABC123 paid
 *   php artisan paymongo:simulate CHK-ABC123 failed
 */
class SimulateWebhookCommand extends Command
{
    protected $signature = 'paymongo:simulate {reference : checkout_reference of the payment} {result=paid : paid or failed}';

    protected $description = 'Local testing: send a signed PayMongo webhook (paid or failed) to this app';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('The webhook simulator is disabled in production.');
            return self::FAILURE;
        }

        $secret = config('services.paymongo.webhook_secret');
        if (!$secret) {
            $this->error('Set PAYMONGO_WEBHOOK_SECRET in .env first (any value works for local testing).');
            return self::FAILURE;
        }

        $payment = Payment::where('checkout_reference', $this->argument('reference'))->first();
        if (!$payment) {
            $this->error('No payment with that checkout reference.');
            return self::FAILURE;
        }

        $result = $this->argument('result');
        if (!in_array($result, ['paid', 'failed'], true)) {
            $this->error('The result must be "paid" or "failed".');
            return self::FAILURE;
        }

        $payload = json_encode($result === 'paid' ? $this->paidEvent($payment) : $this->failedEvent($payment), JSON_UNESCAPED_SLASHES);

        $request = Request::create('/webhooks/paymongo', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => WebhookSignature::sign($payload, $secret),
        ], $payload);

        $started = microtime(true);
        $response = app(Kernel::class)->handle($request);
        $ms = (int) round((microtime(true) - $started) * 1000);

        $this->line("Webhook answered HTTP {$response->getStatusCode()} in {$ms} ms: {$response->getContent()}");
        $payment->refresh();
        $this->info("Payment #{$payment->id} is now: {$payment->status}. Orders: " . $payment->orders()->pluck('payment_status', 'id')->map(fn ($s, $id) => "#{$id} {$s}")->implode(', '));

        return $response->isSuccessful() ? self::SUCCESS : self::FAILURE;
    }

    private function paidEvent(Payment $payment): array
    {
        return $this->event('checkout_session.payment.paid', [
            'id' => $payment->checkout_session_id ?? 'cs_simulated',
            'type' => 'checkout_session',
            'attributes' => [
                'reference_number' => $payment->checkout_reference,
                'metadata' => ['checkout_reference' => $payment->checkout_reference],
                'payment_intent' => ['id' => $payment->payment_intent_id, 'attributes' => ['status' => 'succeeded']],
                'payments' => [[
                    'id' => 'pay_sim_' . Str::random(16),
                    'type' => 'payment',
                    'attributes' => ['status' => 'paid', 'amount' => (int) round($payment->amount * 100), 'currency' => 'PHP'],
                ]],
            ],
        ]);
    }

    private function failedEvent(Payment $payment): array
    {
        return $this->event('payment.failed', [
            'id' => 'pay_sim_' . Str::random(16),
            'type' => 'payment',
            'attributes' => [
                'status' => 'failed',
                'amount' => (int) round($payment->amount * 100),
                'currency' => 'PHP',
                'payment_intent_id' => $payment->payment_intent_id,
                'checkout_session_id' => $payment->checkout_session_id,
                'metadata' => ['checkout_reference' => $payment->checkout_reference],
                'failed_code' => 'card_declined',
                'failed_message' => 'Simulated: the card was declined.',
            ],
        ]);
    }

    private function event(string $type, array $resource): array
    {
        return ['data' => [
            'id' => 'evt_sim_' . Str::random(16),
            'type' => 'event',
            'attributes' => ['type' => $type, 'livemode' => false, 'data' => $resource, 'created_at' => time(), 'updated_at' => time()],
        ]];
    }
}
