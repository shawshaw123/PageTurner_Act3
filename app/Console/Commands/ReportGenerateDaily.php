<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\InvoicePdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySalesReportMail;

class ReportGenerateDaily extends Command
{
    protected $signature = 'report:generate-daily';
    protected $description = 'Generate daily sales report and email to administrators';

    public function handle()
    {
        $this->info('Generating daily sales report...');
        
        try {
            $yesterday = now()->subDay();
            $filters = [
                'date_from' => $yesterday->startOfDay()->toDateString(),
                'date_to' => $yesterday->endOfDay()->toDateString(),
            ];
            
            // Generate PDF report
            $pdfService = new InvoicePdfService();
            $reportPath = $pdfService->generateSalesReport($filters);
            
            // Get statistics
            $orders = Order::whereBetween('created_at', [
                $yesterday->startOfDay(),
                $yesterday->endOfDay()
            ])->get();
            
            $stats = [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total_amount'),
                'completed_orders' => $orders->where('status', 'completed')->count(),
                'pending_orders' => $orders->where('status', 'pending')->count(),
                'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
                'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            ];
            
            // Send email to administrators
            $adminEmails = ['admin@pageturner.com'];
            
            foreach ($adminEmails as $email) {
                try {
                    Mail::to($email)->send(new DailySalesReportMail($stats, $reportPath, $yesterday));
                } catch (\Exception $e) {
                    Log::error("Failed to send daily report to {$email}: " . $e->getMessage());
                }
            }
            
            $this->info("Daily sales report generated successfully for {$yesterday->toDateString()}");
            $this->info("Total Orders: {$stats['total_orders']}, Revenue: \${$stats['total_revenue']}");
            
            Log::info("Daily sales report generated: {$stats['total_orders']} orders, \${$stats['total_revenue']} revenue");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to generate daily sales report: ' . $e->getMessage());
            Log::error('Daily sales report generation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
