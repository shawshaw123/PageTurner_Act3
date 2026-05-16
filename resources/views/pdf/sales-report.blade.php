<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ date('F j, Y') }}</title>
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
        .report-info {
            text-align: right;
            margin-top: 20px;
        }
        .report-title {
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
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
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
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
                <div class="report-info">
                    <div class="report-title">SALES REPORT</div>
                    <div>Generated: {{ now()->format('F j, Y g:i A') }}</div>
                    @if(!empty($filters['date_from']) && !empty($filters['date_to']))
                        <div>Period: {{ $filters['date_from'] }} to {{ $filters['date_to'] }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Executive Summary:</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $totalOrders }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${{ number_format($totalOrders > 0 ? $totalRevenue / $totalOrders : 0, 2) }}</div>
                <div class="stat-label">Average Order Value</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $orders->where('status', 'completed')->count() }}</div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Order Status Breakdown:</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statusBreakdown as $status => $count)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td class="text-right">{{ $count }}</td>
                        <td class="text-right">{{ number_format(($count / $totalOrders) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">Detailed Order List:</div>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->created_at->format('M j, Y') }}</td>
                        <td>{{ $order->user ? $order->user->name : 'Guest' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td class="text-right">{{ $order->items->count() }}</td>
                        <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div><strong>PageTurner Bookstore Sales Report</strong></div>
        <div>This is a computer-generated report.</div>
        <div>Generated on {{ now()->format('F j, Y g:i A') }}</div>
        <div>© {{ date('Y') }} PageTurner Bookstore</div>
    </div>
</body>
</html>
