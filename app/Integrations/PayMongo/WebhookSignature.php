<?php

namespace App\Integrations\PayMongo;

/**
 * Verifies the "Paymongo-Signature" header of a webhook (IP-3).
 *
 * The signature is an HMAC-SHA256 (hex) made with the webhook's secret key
 * (whsk_...). PayMongo's documentation shows two forms, so both are accepted:
 *
 *   t=<timestamp>,te=<test signature>,li=<live signature>
 *       signature of "<timestamp>.<raw body>"
 *   <signature>
 *       signature of "<raw body>"
 *
 * The raw request body must be used, exactly as received.
 */
class WebhookSignature
{
    public static function isValid(string $rawBody, ?string $header, ?string $secret): bool
    {
        if (!$header || !$secret) {
            return false;
        }

        $parts = self::parse($header);

        if (isset($parts['t'])) {
            $expected = hash_hmac('sha256', $parts['t'] . '.' . $rawBody, $secret);
            foreach (['te', 'li'] as $key) {
                if (!empty($parts[$key]) && hash_equals($expected, strtolower($parts[$key]))) {
                    return true;
                }
            }

            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $secret), strtolower(trim($header)));
    }

    /** Build a header in the "t=...,te=..." form (used by the simulator and tests). */
    public static function sign(string $rawBody, string $secret, ?int $timestamp = null, bool $live = false): string
    {
        $timestamp ??= time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);

        return $live ? "t={$timestamp},te=,li={$signature}" : "t={$timestamp},te={$signature},li=";
    }

    /** @return array<string, string> */
    private static function parse(string $header): array
    {
        $parts = [];
        foreach (explode(',', $header) as $piece) {
            if (str_contains($piece, '=')) {
                [$key, $value] = explode('=', $piece, 2);
                $parts[trim($key)] = trim($value);
            }
        }

        return $parts;
    }
}
