<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'stripe' => [
        'enabled' => env('STRIPE_ENABLED', false),
        'test_mode' => env('STRIPE_TEST_MODE', true),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
        'success_url' => env('STRIPE_SUCCESS_URL'),
        'cancel_url' => env('STRIPE_CANCEL_URL'),
    ],

    'cloudflare' => [
        'turnstile' => [
            'enabled' => env('CLOUDFLARE_TURNSTILE_ENABLED', false),
            'site_key' => env('CLOUDFLARE_TURNSTILE_SITE_KEY'),
            'secret_key' => env('CLOUDFLARE_TURNSTILE_SECRET_KEY'),
            'admin_login_enabled' => env('CLOUDFLARE_TURNSTILE_ADMIN_LOGIN', true),
            'customer_login_enabled' => env('CLOUDFLARE_TURNSTILE_CUSTOMER_LOGIN', true),
        ],
    ],

];
