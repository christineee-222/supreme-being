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

    'workos' => [
        'client_id' => env('WORKOS_CLIENT_ID'),
        'secret' => env('WORKOS_API_KEY'),

        'jwt_ttl_seconds' => (int) env('WORKOS_JWT_TTL_SECONDS', 3600),

        // Used by AuthKitLoginRequest::redirect()
        'redirect_url' => env('WORKOS_REDIRECT_URI'),

        // Used by JWT / OAuth callback validation
        'redirect_uri' => env('WORKOS_REDIRECT_URI'),

        'environment' => env('WORKOS_ENV', 'sandbox'),
        'sso_enabled' => env('WORKOS_SSO_ENABLED', false),

    ],

];
