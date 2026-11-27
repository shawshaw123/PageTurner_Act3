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

    // IP-1: book metadata by ISBN. The API key is optional (without it Google
    // applies a lower shared quota).
    'google_books' => [
        'key' => env('GOOGLE_BOOKS_API_KEY'),
        'url' => env('GOOGLE_BOOKS_URL', 'https://www.googleapis.com/books/v1/volumes'),
        'timeout' => (int) env('GOOGLE_BOOKS_TIMEOUT', 8),
    ],

    // IP-2 / IP-3: PayMongo Checkout Sessions and webhooks (use test keys: sk_test_...).
    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'), // whsk_... from the webhook you register
        'url' => env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1'),
        'payment_methods' => array_filter(explode(',', env('PAYMONGO_PAYMENT_METHODS', 'gcash,card,paymaya,grab_pay,qrph'))),
        // Unpaid online orders are expired (and their stock returned) after this many minutes.
        'payment_window_minutes' => (int) env('PAYMONGO_PAYMENT_WINDOW_MINUTES', 60),
    ],

    // IP-6: Google sign-in through Laravel Socialite.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
