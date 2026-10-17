<?php

namespace App\Support;

class Money
{
    /**
     * Format an amount as Bangladeshi Taka, e.g. 560 => "৳560", 559.5 => "৳559.50".
     *
     * Prices are stored as decimals but almost always whole taka, so the
     * fractional part is only shown when it carries meaning.
     */
    public static function bdt(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);
        $decimals = fmod($value, 1) === 0.0 ? 0 : 2;

        return '৳'.number_format($value, $decimals);
    }
}
