<?php

namespace App\Support;

/**
 * Money lives in minor units (тиын, 1/100 ₸) as integers everywhere. This is the
 * single boundary where it becomes a human string, mirroring front/src/lib/money.ts.
 */
class Money
{
    private const SYMBOLS = [
        'KZT' => '₸',
        'USD' => '$',
        'RUB' => '₽',
    ];

    /** Format minor units as e.g. "2 490 ₸" (thin-space grouping, ru-RU style). */
    public static function format(int $minor, string $currency = 'KZT'): string
    {
        $major = intdiv($minor, 100);
        $grouped = number_format($major, 0, '.', ' ');
        $symbol = self::SYMBOLS[$currency] ?? $currency;

        return "{$grouped} {$symbol}";
    }
}
