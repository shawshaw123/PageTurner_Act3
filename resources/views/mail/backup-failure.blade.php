<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Backup Failure Notification</title>
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
            background-color: #dc3545;
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
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            text-align: center;
            margin: 20px 0;
        }
        .error-details {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .error-message {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .stats {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .error-trace {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 11px;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ Backup Failed</h1>
    </div>
    
    <div class="content">
        <div class="error-icon">
            ❌
        </div>
        
        <h2>Backup Failure Alert</h2>
        <p>The {{ $backup->backup_type }} backup for PageTurner Bookstore has failed. Please investigate this issue immediately.</p>
        
        <div class="error-details">
            <h3>⚠️ Immediate Action Required</h3>
            <p>This backup failure may indicate issues with storage, database connectivity, or system resources. Please check the error details below and take corrective action.</p>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <strong>Backup Type:</strong>
                <span>{{ ucfirst($backup->backup_type) }}</span>
            </div>
            <div class="stat-item">
                <strong>Started At:</strong>
                <span>{{ $backup->started_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="stat-item">
                <strong>Failed At:</strong>
                <span>{{ $backup->completed_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="stat-item">
                <strong>Duration Before Failure:</strong>
                <span>{{ $backup->duration_seconds }} seconds</span>
            </div>
            <div class="stat-item">
                <strong>Storage Location:</strong>
                <span>{{ $backup->disk }}</span>
            </div>
        </div>
        
        <div class="error-message">
            <h3>🚨 Error Message:</h3>
            <p>{{ $error }}</p>
        </div>
        
        <details>
            <summary><strong>View Technical Details (Click to expand)</strong></summary>
            <div class="error-trace">{{ $trace }}</div>
        </details>
        
        <p><strong>Recommended Actions:</strong></p>
        <ol>
            <li>Check available disk space on the backup storage</li>
            <li>Verify database connectivity and permissions</li>
            <li>Review system logs for additional errors</li>
            <li>Test manual backup execution</li>
            <li>Consider running a backup immediately after fixing the issue</li>
        </ol>
        
        <p><strong>Manual Backup Command:</strong></p>
        <code>php artisan backup:run-custom --type={{ $backup->backup_type }}</code>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from PageTurner Bookstore Backup System.</p>
        <p>© {{ date('Y') }} PageTurner Bookstore. All rights reserved.</p>
    </div>
</body>
</html>
