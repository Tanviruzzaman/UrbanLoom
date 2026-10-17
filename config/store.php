<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | Shown in the header, footer, page titles and on order confirmations.
    |
    */

    // Change this one value to rebrand the whole site: header, footer, page
    // titles, emails and order confirmations all read from here.
    'name' => env('STORE_NAME', 'Urban Loom'),

    'tagline' => 'Clothing and jewellery, delivered across Bangladesh',

    // Optional logo image. Drop a file in public/images/ and put its path here
    // (e.g. 'images/logo.svg'). Leave blank to show the name as text.
    'logo' => env('STORE_LOGO', ''),

    'phone' => '+880 1700-000000',

    'email' => 'hello@mystore.test',

    'address' => 'Mirpur DOHS, Dhaka 1216, Bangladesh',

    // Used by Open Graph tags when a page has no image of its own.
    'social_image' => 'images/og-default.png',

    /*
    |--------------------------------------------------------------------------
    | Delivery charges (BDT)
    |--------------------------------------------------------------------------
    |
    | Flat rates applied at checkout. Any city that matches one of the
    | `dhaka_aliases` below is charged the inside-Dhaka rate.
    |
    */

    'delivery' => [
        'inside_dhaka' => 60,
        'outside_dhaka' => 120,
        'dhaka_aliases' => ['dhaka', 'ঢাকা', 'dhaka city', 'dhaka metro'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    'cart' => [
        'session_key' => 'cart',
        'max_quantity_per_item' => 10,
    ],
];
