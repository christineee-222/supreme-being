<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Credentials for third-party integrations.
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

    'workos' => [
        'client_id' => env('WORKOS_CLIENT_ID'),
        'api_key' => env('WORKOS_API_KEY'),
        'redirect_url' => env('WORKOS_REDIRECT_URL'),
        'authkit_domain' => rtrim((string) env('WORKOS_AUTHKIT_DOMAIN', ''), '/'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),

        /*
        |--------------------------------------------------------------------------
        | Webhook Secret Handling
        |--------------------------------------------------------------------------
        |
        | Local Stripe CLI testing uses STRIPE_WEBHOOK_SECRET_CLI.
        | Production Stripe dashboard webhooks use STRIPE_WEBHOOK_SECRET.
        | The CLI one takes priority if present.
        |
        */
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET_CLI', env('STRIPE_WEBHOOK_SECRET')),
    ],

];
