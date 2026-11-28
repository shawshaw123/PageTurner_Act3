<?php

namespace App\Integrations\PayMongo;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * The buyer's side of a PayMongo payment (IP-2):
 *  - "Pay now" sends them to PayMongo's hosted payment page;
 *  - PayMongo sends them back to the return or cancelled page.
 *
 * Coming back to the return page is NOT proof of payment: the order only
 * becomes "paid" from the signed webhook, or from PayMongo's own record of the
 * session (checked with the secret key).
 */
class PaymentController extends Controller
{
    /** GET /orders/{order}/pay */
    public function pay(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $payment = $order->payment;
        if (!$order->awaitsOnlinePayment() || !$payment?->isOpen() || !$payment->checkout_url) {
            return redirect()->route('orders.show', $order)->with('error', 'This order has no payment waiting.');
        }

        return redirect()->away($payment->checkout_url);
    }

    /** GET /payments/{reference}/return  (PayMongo success_url) */
    public function return(Request $request, string $reference, PaymentService $payments)
    {
        $payment = $this->paymentFor($request, $reference);
        $payment = $payments->syncFromProvider($payment);

        return view('payments.result', [
            'payment' => $payment,
            'orders' => $payment->orders()->with('seller:id,name,store_name')->get(),
            'cameBack' => 'return',
        ]);
    }

    /** GET /payments/{reference}/cancelled  (PayMongo cancel_url) */
    public function cancelled(Request $request, string $reference)
    {
        $payment = $this->paymentFor($request, $reference);

        return view('payments.result', [
            'payment' => $payment,
            'orders' => $payment->orders()->with('seller:id,name,store_name')->get(),
            'cameBack' => 'cancelled',
        ]);
    }

    /** GET /payments/{reference}/demo  (Local PayMongo Sandbox checkout page) */
    public function demo(Request $request, string $reference, PaymentService $payments)
    {
        abort_unless($payments->isDemoMode(), 404);
        $payment = $this->paymentFor($request, $reference);

        if (!$payment->isOpen()) {
            return redirect()->route('payments.return', $reference);
        }

        return view('payments.demo', [
            'payment' => $payment,
            'orders' => $payment->orders()->with(['orderItems.book', 'seller:id,name,store_name'])->get(),
            'methods' => array_values(config('services.paymongo.payment_methods', ['gcash', 'card', 'paymaya', 'grab_pay', 'qrph'])),
        ]);
    }

    /** POST /payments/{reference}/demo  (Complete or fail a simulated PayMongo payment) */
    public function demoComplete(Request $request, string $reference, PaymentService $payments)
    {
        abort_unless($payments->isDemoMode(), 404);
        $payment = $this->paymentFor($request, $reference);

        $action = $request->input('action', 'pay');
        $channel = strtoupper((string) $request->input('channel', 'GCASH'));

        if ($action === 'fail') {
            $payments->markFailed($payment, "Simulated {$channel} payment declined in local sandbox.");
        } else {
            $payments->markPaid($payment, 'pay_demo_' . \Illuminate\Support\Str::random(16));
        }

        return redirect()->route('payments.return', $reference);
    }

    private function paymentFor(Request $request, string $reference): Payment
    {
        $payment = Payment::where('checkout_reference', $reference)->firstOrFail();
        abort_unless($payment->user_id === $request->user()->id, 403);

        return $payment;
    }
}
