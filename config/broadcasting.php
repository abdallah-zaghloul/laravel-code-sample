<?php

return [
    'default' => env('BROADCAST_DRIVER', 'mercure'),
    'connections' => [
        # WS
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('WS_KEY'),
            'secret' => env('WS_SECRET'),
            'app_id' => env('WS_ID'),
            'options' => [
                'host' => env('APP_HOST', 'localhost'),
                'port' => (int) env('WS_PORT', 8080),
                'scheme' => env('APP_SCHEME', 'https'),
                'useTLS' => env('APP_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],
        # SSE
        'mercure' => [
            'driver' => 'mercure',
            'url' => env('SSE_URL'),
            'secret' => env('JWT_SECRET'),
        ],
        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        'log' => [
            'driver' => 'log',
        ],
        'null' => [
            'driver' => 'null',
        ],
    ],
];
