<?php

declare(strict_types=1);

namespace App\Helpers;

final class Flash
{
    /** @param array<string, mixed> $value */
    public static function set(string $key, array $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /** @return array<string, mixed> */
    public static function get(string $key): array
    {
        $value = $_SESSION['_flash'][$key] ?? [];
        unset($_SESSION['_flash'][$key]);

        return is_array($value) ? $value : [];
    }
}
