<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderCleanupPending extends Command
{
    protected $signature = 'order:cleanup-pending';
    protected $description = 'Cancel pending orders older than 24 hours';

    public function handle()
    {
        $this->info('Starting cleanup of pending orders older than 24 hours...');
        
        $cutoffTime = now()->subHours(24);
        
        $pendingOrders = Order::where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();
        
        $cancelledCount = 0;
        
        foreach ($pendingOrders as $order) {
            try {
                // Restore stock for each item in the order
                foreach ($order->items as $item) {
                    $item->book->increment('stock', $item->quantity);
                }
                
                // Update order status
                $order->update([
                    'status' => 'cancelled',
                    'notes' => 'Automatically cancelled due to being pending for more than 24 hours.'
                ]);
                
                $cancelledCount++;
                
                $this->line("Cancelled order #{$order->order_number}");
                
            } catch (\Exception $e) {
                Log::error("Failed to cancel order #{$order->order_number}: " . $e->getMessage());
                $this->error("Failed to cancel order #{$order->order_number}: " . $e->getMessage());
            }
        }
        
        $this->info("Cleanup completed. Cancelled {$cancelledCount} pending orders.");
        Log::info("Order cleanup completed: {$cancelledCount} pending orders cancelled.");
        
        return 0;
    }
}
