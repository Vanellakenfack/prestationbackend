<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcast Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcasting connection that will be
    | used when an event needs to be broadcast. You may set this to any of
    | the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define the broadcast connections that will be used to broadcast
    | events to your client applications. Each connection type has its own
    | configuration options that are detailed in the Laravel documentation.
    |
    */

    'connections' => [ 

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('c28fdf11a1b85971a160'),
            'secret' => env('98de19abae2e99135be8'),
            'app_id' => env('2027616'),
            'options' => [
                'cluster' => env('eu'),
                'forceTLS' => true,
            ],
            'client_options' => [
                // Options passées au client Pusher JS côté frontend
                'wsHost' => env('PUSHER_APP_HOST') ?: 'ws-'.env('eu').'.pusher.com',
                'wsPort' => env('PUSHER_APP_PORT', 80),
                'wssPort' => env('PUSHER_APP_PORT', 443),
                'forceTLS' => env('PUSHER_APP_SCHEME', 'https') === 'https',
                'enabledTransports' => ['ws', 'wss'],
            ],
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