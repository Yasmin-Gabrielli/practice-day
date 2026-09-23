<?php

declare(strict_types=1);

return [
    'app' => [
        'environment' => env('APP_ENV', 'development'),
        'debug' => filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
        'url' => env('APP_URL', 'http://localhost/practice-day/public'),
        'timezone' => 'America/Sao_Paulo',
    ],
    'database' => require __DIR__ . '/database.php',
    'session' => [
        'name' => 'practice_day_session',
        'options' => [
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOL),
            'use_strict_mode' => true,
        ],
    ],
    'auth' => [
        'session_lifetime_minutes' => 480,
    ],
];
