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

    public static function asset(string $path = '/'): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if (str_ends_with($base, '/public')) {
            $parent = substr($base, 0, -strlen('/public'));
            return ($parent === '' ? '' : $parent) . '/' . ltrim($path, '/');
        }
        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}
