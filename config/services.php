<?php

return [
    'sendcloud' => [
        'key' => env('SENDCLOUD_API_KEY'),
        'user' => env('SENDCLOUD_API_USER'),
        'endpoint' => env('SENDCLOUD_ENDPOINT', 'api.sendcloud.net'),
    ],
];
