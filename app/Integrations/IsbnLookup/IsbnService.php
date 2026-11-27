<?php

namespace App\Integrations\IsbnLookup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IP-1: book metadata by ISBN from the Google Books API.
 *
 * ISBN in; title, author(s), publisher, description and cover URL out.
 * HTTPS GET https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}[&key=...]
 */
class IsbnService
{
    private const CACHE_FOUND_SECONDS = 86400;   // a found book: 1 day
    private const CACHE_MISSING_SECONDS = 3600;  // "not found": 1 hour

    /**
     * @return array<string, mixed>|null  null when Google Books has no book with this ISBN
     *
     * @throws \InvalidArgumentException  when the ISBN is not a valid ISBN-10/13
     * @throws IsbnLookupException        when Google Books cannot be reached or returns an error
     */
    public function lookup(string $isbn): ?array
    {
        $isbn = Isbn::normalize($isbn);

        if (!Isbn::isValid($isbn)) {
            throw new \InvalidArgumentException('That is not a valid ISBN-10 or ISBN-13.');
        }

        $cached = Cache::get($this->cacheKey($isbn));
        if (is_array($cached)) {
            return $cached['found'] ? $cached['book'] : null;
        }

        $book = $this->fetch($isbn);

        Cache::put(
            $this->cacheKey($isbn),
            ['found' => $book !== null, 'book' => $book],
            $book !== null ? self::CACHE_FOUND_SECONDS : self::CACHE_MISSING_SECONDS
        );

        return $book;
    }

    private function fetch(string $isbn): ?array
    {
        $config = config('services.google_books');
        $query = array_filter(['q' => "isbn:{$isbn}", 'key' => $config['key'] ?? null]);

        try {
            $response = Http::timeout($config['timeout'] ?? 8)
                ->acceptJson()
                ->retry(2, 200, throw: false)
                ->get($config['url'], $query);
        } catch (ConnectionException $e) {
            Log::warning('Google Books unreachable', ['isbn' => $isbn, 'error' => $e->getMessage()]);
            throw new IsbnLookupException('Google Books could not be reached. Please fill in the details manually.');
        }

        if ($response->failed()) {
            Log::warning('Google Books error', ['isbn' => $isbn, 'status' => $response->status(), 'body' => mb_substr($response->body(), 0, 500)]);
            throw new IsbnLookupException(
                $response->status() === 429
                    ? 'Google Books limit reached. Please try again later or fill in the details manually.'
                    : 'Google Books returned an error. Please fill in the details manually.'
            );
        }

        $volume = $response->json('items.0.volumeInfo');

        return is_array($volume) ? $this->toBook($isbn, $volume) : null;
    }

    private function toBook(string $isbn, array $volume): array
    {
        $authors = array_values(array_filter((array) ($volume['authors'] ?? [])));
        $title = trim(($volume['title'] ?? '') . (isset($volume['subtitle']) ? ': ' . $volume['subtitle'] : ''));

        return [
            'isbn' => $isbn,
            'title' => $title !== '' ? $title : null,
            'authors' => $authors,
            'author' => $authors ? implode(', ', $authors) : null,
            'publisher' => $volume['publisher'] ?? null,
            'published_date' => $volume['publishedDate'] ?? null,
            'description' => isset($volume['description']) ? trim(strip_tags($volume['description'])) : null,
            'page_count' => $volume['pageCount'] ?? null,
            'language' => $volume['language'] ?? null,
            'cover_url' => $this->coverUrl($volume['imageLinks'] ?? []),
            'source' => 'Google Books',
        ];
    }

    /** Largest available cover, over HTTPS, without Google's page-curl effect. */
    private function coverUrl(array $links): ?string
    {
        foreach (['large', 'medium', 'small', 'thumbnail', 'smallThumbnail'] as $size) {
            if (!empty($links[$size])) {
                $url = preg_replace('#^http://#', 'https://', $links[$size]);
                return str_replace('&edge=curl', '', $url);
            }
        }

        return null;
    }

    private function cacheKey(string $isbn): string
    {
        return "isbn_lookup:{$isbn}";
    }
}
