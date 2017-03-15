<?php

return [
    'url' => env('PHONELOCATOR_URL', ''),
    'map' => [
        '127.0.0.1' => [
            'ip' => env('PHONELOCATOR_IP', ''),
            'url' => env('PHONELOCATOR_URL', '')
        ]
    ]
];