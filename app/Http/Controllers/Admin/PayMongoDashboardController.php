<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\PayMongo\PaymentException;
use App\Integrations\PayMongo\PaymentService;
use App\Models\Payment;
use Illuminate\Http\Request;

class PayMongoDashboardController extends Controller
{
    public function index(Request $request, PaymentService $paymentService)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = Payment::with(['user', 'orders'])
            ->latest();

        if ($status && in_array($status, [Payment::PENDING, Payment::PAID, Payment::FAILED, Payment::EXPIRED], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('checkout_reference', 'like', "%{$search}%")
                    ->orWhere('checkout_session_id', 'like', "%{$search}%")
                    ->orWhere('provider_payment_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $counts = Payment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total_count' => $counts->sum(),
            'paid_count' => (int) ($counts[Payment::PAID] ?? 0),
            'pending_count' => (int) ($counts[Payment::PENDING] ?? 0),
            'failed_count' => (int) ($counts[Payment::FAILED] ?? 0) + (int) ($counts[Payment::EXPIRED] ?? 0),
            'paid_volume' => (float) Payment::where('status', Payment::PAID)->sum('amount'),
        ];

        $config = [
            'configured' => $paymentService->isConfigured(),
            'demo_mode' => $paymentService->isDemoMode(),
            'webhook_configured' => filled(config('services.paymongo.webhook_secret')),
            'is_test_mode' => str_starts_with((string) config('services.paymongo.secret_key'), 'sk_test_'),
            'payment_methods' => array_values(config('services.paymongo.payment_methods', [])),
            'window_minutes' => (int) config('services.paymongo.payment_window_minutes', 60),
            'webhook_url' => route('webhooks.paymongo'),
        ];

        return view('admin.paymongo.index', compact('payments', 'stats', 'counts', 'status', 'config'));
    }

    public function sync(Payment $payment, PaymentService $paymentService)
    {
        if (!$paymentService->isConfigured()) {
            return back()->with('error', 'PayMongo is not configured. Set PAYMONGO_SECRET_KEY in your .env file.');
        }

        $updated = $paymentService->syncFromProvider($payment);

        return back()->with('success', "Payment {$updated->checkout_reference} synced. Current status: " . ucfirst($updated->status) . '.');
    }

    public function expire(Payment $payment, PaymentService $paymentService)
    {
        try {
            $expired = $paymentService->expire($payment);

            return back()->with(
                $expired ? 'success' : 'info',
                $expired
                    ? "Payment {$payment->checkout_reference} expired and stock was returned."
                    : "Payment {$payment->checkout_reference} could not be expired because it is already settled or in progress."
            );
        } catch (PaymentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
