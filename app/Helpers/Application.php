<?php

declare(strict_types=1);

namespace App\Helpers;

final class Application
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @return array<string, mixed> */
    public static function config(): array
    {
        if (self::$config === null) {
            self::$config = require dirname(__DIR__, 2) . '/config/app.php';
        }

        return self::$config;
    }
}
