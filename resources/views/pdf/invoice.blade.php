<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .invoice-info {
            text-align: right;
            margin-top: 20px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .two-column {
            display: flex;
            justify-content: space-between;
        }
        .column {
            width: 48%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="two-column">
            <div class="column">
                <div class="logo">PageTurner Bookstore</div>
                <div>123 Book Street</div>
                <div>Literary City, LC 12345</div>
                <div>Phone: (555) 123-4567</div>
                <div>Email: info@pageturner.com</div>
            </div>
            <div class="column">
                <div class="invoice-info">
                    <div class="invoice-number">INVOICE #{{ $order->order_number }}</div>
                    <div>Date: {{ $order->created_at->format('F j, Y') }}</div>
                    <div>Status: {{ ucfirst($order->status) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="two-column">
            <div class="column">
                <div class="section-title">Bill To:</div>
                <div>
                    @if($order->user)
                        <div>{{ $order->user->name }}</div>
                        <div>{{ $order->user->email }}</div>
                        @if($order->user->phone)
                            <div>{{ $order->user->phone }}</div>
                        @endif
                    @else
                        <div>Guest Customer</div>
                    @endif
                </div>
            </div>
            <div class="column">
                <div class="section-title">Ship To:</div>
                <div>
                    @if($order->shipping_address)
                        <div>{{ $order->shipping_address['address_line_1'] ?? '' }}</div>
                        @if($order->shipping_address['address_line_2'] ?? null)
                            <div>{{ $order->shipping_address['address_line_2'] }}</div>
                        @endif
                        <div>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}</div>
                        <div>{{ $order->shipping_address['country'] ?? '' }}</div>
                    @else
                        <div>No shipping address provided</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Order Items:</div>
        <table>
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->book->isbn }}</td>
                        <td>{{ $item->book->title }}</td>
                        <td>{{ $item->book->author }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-right">Subtotal:</td>
                    <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right">Shipping:</td>
                    <td class="text-right">${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right">Tax:</td>
                    <td class="text-right">${{ number_format($order->tax_amount ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-right"><strong>Total:</strong></td>
                    <td class="text-right"><strong>${{ number_format($order->total_amount + ($order->shipping_cost ?? 0) + ($order->tax_amount ?? 0), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Payment Information:</div>
        <div>
            <div><strong>Payment Method:</strong> {{ ucfirst($order->payment_method ?? 'Standard') }}</div>
            <div><strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'Pending') }}</div>
        </div>
    </div>

    <div class="footer">
        <div><strong>Thank you for your order!</strong></div>
        <div>This is a computer-generated invoice and does not require a signature.</div>
        <div>For questions about this invoice, please contact our customer service.</div>
        <div>PageTurner Bookstore © {{ date('Y') }}</div>
    </div>
</body>
</html>
