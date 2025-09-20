<?php
return [
    'transports' => [
        'web' => [
            'enabled' => true,
            'middleware' => ['api'],
            'prefix' => 'ai',
        ],
        'stdio' => [
            'enabled' => true,
        ],
    ],
    'servers' => [
        'eco-tracker' => [
            'class' => \App\Mcp\Servers\EcoTrackerServer::class,
            'middleware' => ['auth:sanctum'],
        ],
    ],
];