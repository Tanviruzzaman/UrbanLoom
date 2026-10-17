<?php

namespace App\Support;

use Illuminate\Support\Str;

class Delivery
{
    /**
     * Flat delivery charge for a city: the inside-Dhaka rate when the city
     * matches one of the configured aliases, otherwise the outside rate.
     */
    public static function chargeFor(?string $city): float
    {
        $normalised = Str::lower(trim((string) $city));

        $aliases = array_map(
            fn ($alias) => Str::lower($alias),
            config('store.delivery.dhaka_aliases', ['dhaka']),
        );

        return in_array($normalised, $aliases, true)
            ? (float) config('store.delivery.inside_dhaka')
            : (float) config('store.delivery.outside_dhaka');
    }

    public static function isInsideDhaka(?string $city): bool
    {
        return static::chargeFor($city) === (float) config('store.delivery.inside_dhaka');
    }

    /**
     * Cities offered in the checkout dropdown. Dhaka first so the common
     * case is one tap on mobile.
     *
     * @return array<int, string>
     */
    public static function cities(): array
    {
        return [
            'Dhaka', 'Chattogram', 'Khulna', 'Rajshahi', 'Sylhet',
            'Barishal', 'Rangpur', 'Mymensingh', 'Gazipur', 'Narayanganj',
            'Cumilla', 'Cox\'s Bazar', 'Jashore', 'Bogura', 'Dinajpur',
        ];
    }
}
