<?php

namespace App\Integrations\IsbnLookup;

use Illuminate\Console\Command;

/**
 * Objective 2 evidence: how many test ISBNs return a title, an author and a cover.
 *
 *   php artisan isbn:test-lookup 9780593135204 9781635573175
 *   php artisan isbn:test-lookup --file=storage/app/test-isbns.txt   (one ISBN per line)
 */
class TestIsbnLookupCommand extends Command
{
    protected $signature = 'isbn:test-lookup {isbns?* : ISBNs to look up} {--file= : Text file with one ISBN per line}';

    protected $description = 'IP-1: look up ISBNs on Google Books and report how many return title, author and cover';

    public function handle(IsbnService $isbnService): int
    {
        $isbns = $this->argument('isbns');

        if ($file = $this->option('file')) {
            if (!is_readable($file)) {
                $this->error("Cannot read {$file}");
                return self::FAILURE;
            }
            $isbns = array_merge($isbns, preg_split('/\R/', trim(file_get_contents($file))));
        }

        $isbns = array_values(array_unique(array_filter(array_map('trim', $isbns))));
        if (!$isbns) {
            $this->error('Give some ISBNs, or --file=path.');
            return self::FAILURE;
        }

        $rows = [];
        $complete = 0;
        $started = microtime(true);

        foreach ($isbns as $isbn) {
            $t = microtime(true);
            try {
                $book = $isbnService->lookup($isbn);
                $result = $book === null ? 'not found' : 'found';
            } catch (\Throwable $e) {
                $book = null;
                $result = 'error: ' . $e->getMessage();
            }

            $hasAll = $book && $book['title'] && $book['author'] && $book['cover_url'];
            $complete += $hasAll ? 1 : 0;

            $rows[] = [
                $isbn,
                $result,
                $book ? mb_strimwidth((string) $book['title'], 0, 40, '…') : '',
                $book['author'] ?? '' ? 'yes' : 'no',
                $book['cover_url'] ?? '' ? 'yes' : 'no',
                $hasAll ? 'PASS' : 'FAIL',
                (int) round((microtime(true) - $t) * 1000) . ' ms',
            ];
        }

        $this->table(['ISBN', 'Result', 'Title', 'Author', 'Cover', 'Title+author+cover', 'Time'], $rows);

        $rate = round($complete / count($isbns) * 100, 1);
        $this->line(sprintf(
            '%d of %d ISBNs (%s%%) returned title, author and cover. Target: at least 90%%. Total time %.1f s.',
            $complete, count($isbns), $rate, microtime(true) - $started
        ));

        return $rate >= 90 ? self::SUCCESS : self::FAILURE;
    }
}
