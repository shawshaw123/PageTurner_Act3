<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .attachment-notice {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Daily Sales Report</h1>
        <p>{{ $date->format('l, F j, Y') }}</p>
    </div>
    
    <div class="content">
        <h2>Sales Performance Summary</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${{ number_format($stats['total_revenue'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${{ number_format($stats['average_order_value'], 2) }}</div>
                <div class="stat-label">Average Order Value</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['completed_orders'] }}</div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>
        
        <h3>Order Status Breakdown</h3>
        <ul>
            <li><strong>Completed:</strong> {{ $stats['completed_orders'] }} orders</li>
            <li><strong>Pending:</strong> {{ $stats['pending_orders'] }} orders</li>
            <li><strong>Cancelled:</strong> {{ $stats['cancelled_orders'] }} orders</li>
        </ul>
        
        <div class="attachment-notice">
            <h3>📎 Detailed Report Attached</h3>
            <p>A comprehensive PDF report with complete order details and breakdowns is attached to this email.</p>
        </div>
        
        <h3>Key Insights</h3>
        @if($stats['total_orders'] > 0)
            <p>• Average order value: ${{ number_format($stats['average_order_value'], 2) }}</p>
            <p>• Completion rate: {{ number_format(($stats['completed_orders'] / $stats['total_orders']) * 100, 1) }}%</p>
        @else
            <p>No orders were processed on this day.</p>
        @endif
        
        <p><strong>Recommendations:</strong></p>
        @if($stats['pending_orders'] > $stats['completed_orders'])
            <p>• Consider following up on pending orders to improve completion rate</p>
        @endif
        @if($stats['cancelled_orders'] > 0)
            <p>• Review cancelled orders to identify potential issues</p>
        @endif
    </div>
    
    <div class="footer">
        <p>This is an automated daily sales report from PageTurner Bookstore.</p>
        <p>© {{ date('Y') }} PageTurner Bookstore. All rights reserved.</p>
    </div>
</body>
</html>
