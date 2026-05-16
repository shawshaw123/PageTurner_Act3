<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupPendingOrdersCommand extends Command
{
    protected $signature = 'order:cleanup-pending {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Cancel pending orders older than 24 hours';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting cleanup of pending orders...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No orders will be actually cancelled');
        }

        try {
            // Find pending orders older than 24 hours
            $cutoffTime = now()->subHours(24);
            $pendingOrders = Order::where('status', 'pending')
                                  ->where('created_at', '<', $cutoffTime)
                                  ->get();

            $count = $pendingOrders->count();
            
            if ($count === 0) {
                $this->info('No pending orders older than 24 hours found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$count} pending orders older than 24 hours:");

            foreach ($pendingOrders as $order) {
                $this->line("Order #{$order->order_number} - Created: {$order->created_at->format('Y-m-d H:i:s')} - Customer: " . ($order->user ? $order->user->email : 'Guest'));
            }

            if (!$dryRun) {
                $this->info('Cancelling orders...');
                
                foreach ($pendingOrders as $order) {
                    $order->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Automatically cancelled due to pending status for over 24 hours'
                    ]);

                    // Log the cancellation
                    Log::info('Order automatically cancelled', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'reason' => 'Pending for over 24 hours',
                        'cancelled_at' => now()
                    ]);

                    // TODO: Send notification to customer about order cancellation
                    // This could be implemented later as part of the notification system
                }

                $this->info("Successfully cancelled {$count} pending orders.");
            } else {
                $this->info("Would cancel {$count} pending orders in dry run mode.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to cleanup pending orders: ' . $e->getMessage());
            Log::error('Pending orders cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
