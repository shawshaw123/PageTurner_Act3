<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function generateInvoice(Order $order): string
    {
        $pdf = PDF::loadView('pdf.invoice', compact('order'));
        
        $filename = 'invoice_' . $order->order_number . '.pdf';
        $path = 'invoices/' . $filename;
        
        // Store the PDF
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }

    public function generateBulkInvoices(array $orderIds): string
    {
        $orders = Order::with(['user', 'items.book'])->whereIn('id', $orderIds)->get();
        
        $pdf = PDF::loadView('pdf.bulk-invoices', compact('orders'));
        
        $filename = 'bulk_invoices_' . date('Y-m-d_H-i-s') . '.pdf';
        $path = 'invoices/' . $filename;
        
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }

    public function generateSalesReport(array $filters): string
    {
        $query = Order::query()->with(['user', 'items.book']);

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate statistics
        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $statusBreakdown = $orders->groupBy('status')->map->count();
        
        $pdf = PDF::loadView('pdf.sales-report', compact('orders', 'filters', 'totalRevenue', 'totalOrders', 'statusBreakdown'));
        
        $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.pdf';
        $path = 'reports/' . $filename;
        
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }
}
