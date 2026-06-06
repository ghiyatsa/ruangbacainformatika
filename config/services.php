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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', env('GOOGLE_REDIRECT_URI', '/auth/google/callback')),
    ],
    'similarity_api' => [
        'url' => env('SIMILARITY_API_URL', 'http://localhost:8181'),
        'secret' => env('SIMILARITY_API_SECRET', 'changeme-secret-token'),
        'timeout' => env('SIMILARITY_API_TIMEOUT', 10),
        'dispatch' => env('SIMILARITY_SYNC_DISPATCH', 'auto'),
    ],

    'fonnte' => [
        'url' => env('FONNTE_API_URL'),
        'token' => env('FONNTE_API_TOKEN'),
        'send_interval_seconds' => env('FONNTE_SEND_INTERVAL_SECONDS', 15),
        'failure_pause_threshold' => env('FONNTE_FAILURE_PAUSE_THRESHOLD', 5),
        'failure_pause_window_minutes' => env('FONNTE_FAILURE_PAUSE_WINDOW_MINUTES', 15),
    ],

    'huggingface' => [
        'token' => env('HF_TOKEN'),
    ],

    'turnstile' => [
        'key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

];
