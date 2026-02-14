<?php

return [
    'google_civic' => [
        'api_key' => env('GOOGLE_CIVIC_API_KEY'),
        'base_url' => env('GOOGLE_CIVIC_BASE_URL', 'https://www.googleapis.com/civicinfo/v2'),
        'cache_minutes' => env('BALLOT_CACHE_MINUTES', 30),
    ],
];
