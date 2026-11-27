<?php

namespace App\Integrations\Email;

use App\Models\NotificationLog;
use Illuminate\Console\Command;

/**
 * Objective 4 evidence: how many emails were sent within 60 seconds of being queued.
 *
 *   php artisan mail:delivery-report
 *   php artisan mail:delivery-report --since="2026-11-27 08:00"
 */
class DeliveryReportCommand extends Command
{
    protected $signature = 'mail:delivery-report {--since= : Only emails queued after this date/time} {--limit=60 : Seconds allowed}';

    protected $description = 'IP-4: report email delivery times from notification_logs (target: 95% within 60 s)';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $logs = NotificationLog::query()
            ->when($this->option('since'), fn ($q, $since) => $q->where('created_at', '>=', $since))
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No emails logged yet. Place, pay or update some orders with the queue worker running (php artisan queue:work).');
            return self::SUCCESS;
        }

        $rows = $logs->groupBy('notification')->map(fn ($group, $name) => [
            $name,
            $group->count(),
            $group->where('status', 'failed')->count(),
            $group->where('status', 'sent')->whereNotNull('delivery_seconds')->where('delivery_seconds', '<=', $limit)->count(),
            round((float) $group->whereNotNull('delivery_seconds')->avg('delivery_seconds'), 1) . ' s',
            ($group->max('delivery_seconds') ?? '-') . ' s',
        ])->values()->all();

        $this->table(['Email', 'Total', 'Failed', "Sent within {$limit}s", 'Average', 'Slowest'], $rows);

        $onTime = $logs->where('status', 'sent')->whereNotNull('delivery_seconds')->where('delivery_seconds', '<=', $limit)->count();
        $rate = round($onTime / $logs->count() * 100, 1);
        $this->line("{$onTime} of {$logs->count()} emails ({$rate}%) were sent within {$limit} seconds. Target: at least 95%.");

        return $rate >= 95 ? self::SUCCESS : self::FAILURE;
    }
}
