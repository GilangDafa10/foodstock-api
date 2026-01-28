<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        env('FRONTEND_URL'), // Untuk production
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    'max_age' => 0,

    // ✅ WAJIB true untuk Sanctum SPA
    'supports_credentials' => true,
];
