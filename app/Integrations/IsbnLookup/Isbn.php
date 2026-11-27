<?php

namespace App\Integrations\IsbnLookup;

/** Cleans up and validates ISBN-10 and ISBN-13 numbers (including the check digit). */
class Isbn
{
    /** Remove hyphens and spaces; upper-case a trailing x. */
    public static function normalize(string $isbn): string
    {
        return strtoupper(preg_replace('/[\s-]+/', '', trim($isbn)));
    }

    public static function isValid(string $isbn): bool
    {
        $isbn = self::normalize($isbn);

        if (preg_match('/^\d{9}[\dX]$/', $isbn)) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
                $sum += $digit * (10 - $i);
            }
            return $sum % 11 === 0;
        }

        if (preg_match('/^97[89]\d{10}$/', $isbn)) {
            $sum = 0;
            for ($i = 0; $i < 13; $i++) {
                $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            return $sum % 10 === 0;
        }

        return false;
    }
}
