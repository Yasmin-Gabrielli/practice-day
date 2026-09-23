<?php

declare(strict_types=1);

/**
 * Credenciais são lidas exclusivamente das variáveis de ambiente/.env.
 * Este arquivo não cria nem altera o banco ou suas tabelas.
 */
return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'practice_day'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
