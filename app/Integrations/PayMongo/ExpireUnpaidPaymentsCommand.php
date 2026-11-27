<?php

namespace App\Integrations\PayMongo;

use App\Models\Payment;
use Illuminate\Console\Command;

/**
 * Scheduler (cron): expire unpaid online orders and return their stock.
 *
 * PayMongo sends no webhook when a checkout session expires, so this runs every
 * five minutes (routes/console.php) and closes payments still unpaid after the
 * payment window (PAYMONGO_PAYMENT_WINDOW_MINUTES, default 60).
 */
class ExpireUnpaidPaymentsCommand extends Command
{
    protected $signature = 'payments:expire-unpaid {--dry-run : List what would be expired without changing anything}';

    protected $description = 'Scheduler: expire unpaid PayMongo orders after the payment window and return their stock';

    public function handle(PaymentService $payments): int
    {
        $minutes = (int) config('services.paymongo.payment_window_minutes', 60);

        $due = Payment::whereIn('status', [Payment::PENDING, Payment::FAILED])
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->orderBy('id')
            ->get();

        if ($due->isEmpty()) {
            $this->info("No unpaid payments older than {$minutes} minutes.");
            return self::SUCCESS;
        }

        $expired = 0;
        foreach ($due as $payment) {
            if ($this->option('dry-run')) {
                $this->line("Would expire payment #{$payment->id} ({$payment->checkout_reference}), created {$payment->created_at->diffForHumans()}");
                continue;
            }

            try {
                if ($payments->expire($payment)) {
                    $expired++;
                    $this->line("Expired payment #{$payment->id} ({$payment->checkout_reference}); orders cancelled and stock returned.");
                } else {
                    $this->line("Skipped payment #{$payment->id}: PayMongo reports a payment in progress or completed.");
                }
            } catch (PaymentException $e) {
                $this->warn("Payment #{$payment->id}: {$e->getMessage()} Will retry on the next run.");
            }
        }

        $this->info("{$expired} payment(s) expired.");

        return self::SUCCESS;
    }
}
