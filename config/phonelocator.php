<?php

return [
    'url' => env('PHONELOCATOR_URL', ''),
    'map' => [
        env('PHONELOCATOR_CLIENT_IP', '127.0.0.1') => [
            'ip' => env('PHONELOCATOR_PHONE_IP', ''),
            'url' => env('PHONELOCATOR_URL', '')
        ]
    ]
];