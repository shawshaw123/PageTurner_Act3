<?php

namespace App\Integrations\IsbnLookup;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Saves the cover found by the ISBN lookup as the listing's cover image.
 *
 * Only HTTPS URLs on Google's book-cover hosts are fetched, without following
 * redirects, so a submitted form cannot make the server download from
 * arbitrary or internal addresses.
 */
class CoverDownloader
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const ALLOWED_HOSTS = ['books.google.com', 'books.googleusercontent.com'];

    public static function isAllowedUrl(?string $url): bool
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https'
            && in_array(strtolower($parts['host'] ?? ''), self::ALLOWED_HOSTS, true)
            && !isset($parts['user'], $parts['pass'])
            && !isset($parts['port']);
    }

    /** @return string|null  storage path of the saved cover, or null if it could not be downloaded */
    public function download(string $url): ?string
    {
        if (!self::isAllowedUrl($url)) {
            return null;
        }

        try {
            $response = Http::timeout(10)->withOptions(['allow_redirects' => false])->get($url);
        } catch (\Throwable $e) {
            Log::info('Cover download failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }

        $body = $response->body();
        $type = strtolower((string) $response->header('Content-Type'));

        if (!$response->successful() || !str_starts_with($type, 'image/') || $body === '' || strlen($body) > self::MAX_BYTES) {
            return null;
        }

        // Make sure it really is an image before storing it.
        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        file_put_contents($tmp, $body);

        try {
            if (@getimagesize($tmp) === false) {
                return null;
            }

            return ImageService::processBookCover(new UploadedFile($tmp, 'cover.jpg', $type, null, true));
        } finally {
            @unlink($tmp);
        }
    }
}
