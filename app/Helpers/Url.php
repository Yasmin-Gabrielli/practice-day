<?php

declare(strict_types=1);

namespace App\Helpers;

final class Url
{
    public static function to(string $path = '/'): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}
