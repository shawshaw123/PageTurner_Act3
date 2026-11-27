<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Backup Success Notification</title>
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
            background-color: #28a745;
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
        .success-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Backup Completed Successfully</h1>
    </div>
    
    <div class="content">
        <div class="success-icon">
            ✅
        </div>
        
        <h2>Backup Summary</h2>
        <p>The {{ $backup->backup_type }} backup for PageTurner Bookstore has been completed successfully.</p>
        
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
                <strong>Completed At:</strong>
                <span>{{ $backup->completed_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="stat-item">
                <strong>Duration:</strong>
                <span>{{ $backup->duration_seconds }} seconds</span>
            </div>
            <div class="stat-item">
                <strong>Backup Size:</strong>
                <span>{{ $size }}</span>
            </div>
            <div class="stat-item">
                <strong>Storage Location:</strong>
                <span>{{ $backup->disk }}</span>
            </div>
            @if($backup->path)
            <div class="stat-item">
                <strong>File Path:</strong>
                <span>{{ $backup->path }}</span>
            </div>
            @endif
        </div>
        
        <p>This backup includes your database files and application files. The backup has been stored securely and is ready for restoration if needed.</p>
        
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Verify backup integrity if needed</li>
            <li>Monitor storage space usage</li>
            <li>Review backup retention policy</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from PageTurner Bookstore Backup System.</p>
        <p>© {{ date('Y') }} PageTurner Bookstore. All rights reserved.</p>
    </div>
</body>
</html>
