<?php

return [

    'default' => env('REVERB_SERVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers (INTERNAL)
    |--------------------------------------------------------------------------
    */
    'servers' => [
        'reverb' => [
            'host' => env('BIND_HOST', '0.0.0.0'),
            'port' => (int) env('WS_PORT', 8080),
            'hostname' => env('APP_HOST', 'localhost'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('WS_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('WS_SCALING_ENABLED', false),
                'channel' => env('WS_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', 'localhost'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                    'timeout' => env('REDIS_TIMEOUT', 60),
                ],
            ],
            'pulse_ingest_interval' => env('WS_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('WS_TELESCOPE_INGEST_INTERVAL', 15),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Applications (PUBLIC CLIENT CONFIG)
    |--------------------------------------------------------------------------
    */
    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'key' => env('WS_KEY'),
                'secret' => env('WS_SECRET'),
                'app_id' => env('WS_ID'),
                'options' => [
                    'host' => env('APP_HOST'),
                    'port' => env('WS_PORT'),
                    'scheme' => env('APP_SCHEME', 'https'),
                    'useTLS' => env('APP_SCHEME', 'https') === 'https',
                ],
                'allowed_origins' => ['*'],
                'ping_interval' => env('WS_APP_PING_INTERVAL', 60),
                'activity_timeout' => env('WS_APP_ACTIVITY_TIMEOUT', 90),
                'max_connections' => env('WS_APP_MAX_CONNECTIONS', 5000),
                'max_message_size' => env('WS_APP_MAX_MESSAGE_SIZE', 10_000),
            ]
        ],
    ],

];
