<?php

use App\Support\Delivery;
use App\Support\Money;

/*
|--------------------------------------------------------------------------
| View helpers
|--------------------------------------------------------------------------
|
| Blade compiles component slot content into a closure, and PHP does not allow
| `use` imports inside a closure body. These helpers let templates format money
| and delivery charges without importing anything.
|
*/

if (! function_exists('bdt')) {
    /**
     * Format an amount as Bangladeshi Taka: bdt(560) => "৳560".
     */
    function bdt(float|int|string|null $amount): string
    {
        return Money::bdt($amount);
    }
}

if (! function_exists('delivery_charge')) {
    /**
     * Flat delivery charge for a city (৳60 inside Dhaka, ৳120 outside).
     */
    function delivery_charge(?string $city): float
    {
        return Delivery::chargeFor($city);
    }
}
