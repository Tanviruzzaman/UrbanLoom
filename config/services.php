<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulk SMS gateway
    |--------------------------------------------------------------------------
    |
    | Generic HTTP gateway. Leave the credentials blank and SmsService logs the
    | message instead of sending it, so local development never needs an account.
    |
    */

    'sms' => [
        'url' => env('SMS_API_URL', ''),
        'key' => env('SMS_API_KEY', ''),
        'sender_id' => env('SMS_SENDER_ID', ''),
        'timeout' => (int) env('SMS_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Steadfast courier
    |--------------------------------------------------------------------------
    |
    | Merchant API for booking parcels. Leave the credentials blank and the
    | "Book Courier" action reports that it is not configured rather than failing.
    |
    */

    'steadfast' => [
        'base_url' => env('STEADFAST_BASE_URL', 'https://portal.steadfast.com.bd/api/v1'),
        'api_key' => env('STEADFAST_API_KEY', ''),
        'secret' => env('STEADFAST_SECRET', ''),
        'timeout' => (int) env('STEADFAST_TIMEOUT', 15),
    ],

];
