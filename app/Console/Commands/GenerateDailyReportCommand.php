<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Book;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateDailyReportCommand extends Command
{
    protected $signature = 'report:generate-daily {--date= : Date for the report (Y-m-d format, defaults to yesterday)}';
    protected $description = 'Generate daily sales and system report';

    public function handle()
    {
        $date = $this->option('date') ?: now()->subDay()->format('Y-m-d');
        $reportDate = \Carbon\Carbon::parse($date);

        $this->info("Generating daily report for {$reportDate->format('Y-m-d')}...");

        try {
            // Sales statistics
            $salesStats = $this->getSalesStats($date);
            
            // New users statistics
            $userStats = $this->getUserStats($date);
            
            // Book statistics
            $bookStats = $this->getBookStats($date);
            
            // System statistics
            $systemStats = $this->getSystemStats();

            // Prepare report data
            $reportData = [
                'date' => $reportDate->format('Y-m-d'),
                'report_date' => $reportDate->format('F j, Y'),
                'sales' => $salesStats,
                'users' => $userStats,
                'books' => $bookStats,
                'system' => $systemStats,
            ];

            // Generate report content
            $reportContent = $this->generateReportContent($reportData);

            // Save report to file
            $reportPath = storage_path("app/reports/daily_report_{$date}.html");
            $this->ensureDirectoryExists(dirname($reportPath));
            file_put_contents($reportPath, $reportContent);

            // Send email to administrators
            $this->sendReportEmail($reportData, $reportDate);

            $this->info("Daily report generated successfully for {$reportDate->format('Y-m-d')}");
            $this->info("Report saved to: {$reportPath}");

            Log::info('Daily report generated', [
                'date' => $date,
                'total_orders' => $salesStats['total_orders'],
                'total_revenue' => $salesStats['total_revenue'],
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to generate daily report: " . $e->getMessage());
            Log::error('Daily report generation failed', [
                'date' => $date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    protected function getSalesStats($date)
    {
        $orders = Order::whereDate('created_at', $date)->get();
        
        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
        ];
    }

    protected function getUserStats($date)
    {
        return [
            'new_users' => User::whereDate('created_at', $date)->count(),
            'total_users' => User::count(),
            'active_users' => User::whereDate('last_login_at', $date)->count(),
        ];
    }

    protected function getBookStats($date)
    {
        return [
            'total_books' => Book::count(),
            'new_books' => Book::whereDate('created_at', $date)->count(),
            'low_stock_books' => Book::where('stock', '<', 10)->where('stock', '>', 0)->count(),
            'out_of_stock_books' => Book::where('stock', 0)->count(),
        ];
    }

    protected function getSystemStats()
    {
        return [
            'database_size' => $this->getDatabaseSize(),
            'storage_usage' => $this->getStorageUsage(),
        ];
    }

    protected function getDatabaseSize()
    {
        try {
            $result = DB::select('SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = ?', [env('DB_DATABASE')]);
            return $result[0]->size ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getStorageUsage()
    {
        $storagePath = storage_path();
        $totalSize = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($storagePath));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // Handle permission issues
        }

        return $totalSize;
    }

    protected function generateReportContent($data)
    {
        $html = "<!DOCTYPE html><html><head><title>Daily Report - {$data['report_date']}</title>";
        $html .= "<style>body{font-family:Arial,sans-serif;margin:20px;} .header{background:#f4f4f4;padding:20px;border-radius:5px;} ";
        $html .= ".section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;} ";
        $html .= ".stats{display:flex;gap:20px;flex-wrap:wrap;} .stat-box{background:#f9f9f9;padding:15px;border-radius:5px;min-width:200px;} ";
        $html .= "h2{color:#333;} h3{color:#666;} .stat-value{font-size:24px;font-weight:bold;color:#007bff;}</style></head><body>";
        
        $html .= "<div class='header'><h1>PageTurner Daily Report</h1><p>Date: {$data['report_date']}</p></div>";
        
        // Sales Section
        $html .= "<div class='section'><h2>Sales Overview</h2><div class='stats'>";
        $html .= "<div class='stat-box'><h3>Total Orders</h3><div class='stat-value'>{$data['sales']['total_orders']}</div></div>";
        $html .= "<div class='stat-box'><h3>Total Revenue</h3><div class='stat-value'>$" . number_format($data['sales']['total_revenue'], 2) . "</div></div>";
        $html .= "<div class='stat-box'><h3>Avg Order Value</h3><div class='stat-value'>$" . number_format($data['sales']['average_order_value'], 2) . "</div></div>";
        $html .= "<div class='stat-box'><h3>Completed Orders</h3><div class='stat-value'>{$data['sales']['completed_orders']}</div></div>";
        $html .= "</div></div>";
        
        // Users Section
        $html .= "<div class='section'><h2>User Activity</h2><div class='stats'>";
        $html .= "<div class='stat-box'><h3>New Users</h3><div class='stat-value'>{$data['users']['new_users']}</div></div>";
        $html .= "<div class='stat-box'><h3>Total Users</h3><div class='stat-value'>{$data['users']['total_users']}</div></div>";
        $html .= "<div class='stat-box'><h3>Active Users</h3><div class='stat-value'>{$data['users']['active_users']}</div></div>";
        $html .= "</div></div>";
        
        // Books Section
        $html .= "<div class='section'><h2>Book Inventory</h2><div class='stats'>";
        $html .= "<div class='stat-box'><h3>Total Books</h3><div class='stat-value'>{$data['books']['total_books']}</div></div>";
        $html .= "<div class='stat-box'><h3>New Books</h3><div class='stat-value'>{$data['books']['new_books']}</div></div>";
        $html .= "<div class='stat-box'><h3>Low Stock</h3><div class='stat-value'>{$data['books']['low_stock_books']}</div></div>";
        $html .= "<div class='stat-box'><h3>Out of Stock</h3><div class='stat-value'>{$data['books']['out_of_stock_books']}</div></div>";
        $html .= "</div></div>";
        
        $html .= "</body></html>";
        
        return $html;
    }

    protected function sendReportEmail($data, $reportDate)
    {
        try {
            $adminEmails = User::where('role', 'admin')->pluck('email')->toArray();
            
            if (empty($adminEmails)) {
                $this->warn('No admin users found to send report to');
                return;
            }

            // TODO: Implement email notification
            // This would require creating a mailable class and sending it
            // For now, we'll log that the report would be sent
            Log::info('Daily report email would be sent to admins', [
                'admins' => $adminEmails,
                'date' => $reportDate->format('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send daily report email', [
                'error' => $e->getMessage(),
                'date' => $reportDate->format('Y-m-d'),
            ]);
        }
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
