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

    private function paymentFor(Request $request, string $reference): Payment
    {
        $payment = Payment::where('checkout_reference', $reference)->firstOrFail();
        abort_unless($payment->user_id === $request->user()->id, 403);

        return $payment;
    }
}
