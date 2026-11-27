<?php

namespace App\Integrations\IsbnLookup;

use App\Models\Book;
use Illuminate\Console\Command;

/**
 * Objective 2 evidence: average time to create a listing with and without the ISBN lookup.
 * The Add Listing form records how long each listing took (books.listing_seconds).
 *
 *   php artisan isbn:listing-report
 */
class ListingTimeReportCommand extends Command
{
    protected $signature = 'isbn:listing-report';

    protected $description = 'IP-1: compare average listing time with and without the ISBN lookup';

    public function handle(): int
    {
        $stats = Book::withoutGlobalScopes()
            ->whereNotNull('listing_seconds')
            ->selectRaw('isbn_lookup_used, COUNT(*) as listings, AVG(listing_seconds) as average, MIN(listing_seconds) as fastest, MAX(listing_seconds) as slowest')
            ->groupBy('isbn_lookup_used')
            ->get()
            ->keyBy(fn ($row) => $row->isbn_lookup_used ? 'lookup' : 'manual');

        $rows = [];
        foreach (['manual' => 'Manual entry', 'lookup' => 'With ISBN lookup'] as $key => $label) {
            $row = $stats[$key] ?? null;
            $rows[] = [$label, $row->listings ?? 0, $row ? round($row->average, 1) . ' s' : '-', $row->fastest ?? '-', $row->slowest ?? '-'];
        }
        $this->table(['Method', 'Listings', 'Average', 'Fastest (s)', 'Slowest (s)'], $rows);

        if (!isset($stats['manual'], $stats['lookup'])) {
            $this->line('Create some listings both ways (with and without "Look up") to compare. Target: 10 sample listings each.');
            return self::SUCCESS;
        }

        $saving = (1 - $stats['lookup']->average / $stats['manual']->average) * 100;
        $this->line(sprintf('The ISBN lookup is %.1f%% faster on average. Target: at least 30%%.', $saving));

        return $saving >= 30 ? self::SUCCESS : self::FAILURE;
    }
}
