<?php

declare(strict_types=1);

namespace App\Helpers;

final class Lang
{
    /** @var array<string, string>|null */
    private static ?array $messages = null;
    private static ?string $loadedLocale = null;

    public static function locale(): string
    {
        $locale = (string) (($_SESSION['user_settings']['idioma'] ?? null) ?: 'pt-BR');

        return in_array($locale, ['pt-BR', 'en-US'], true) ? $locale : 'pt-BR';
    }

    public static function get(string $key, array $replace = []): string
    {
        $locale = self::locale();

        if (self::$loadedLocale !== $locale || self::$messages === null) {
            $base = require dirname(__DIR__, 2) . '/config/lang/pt-BR.php';
            if ($locale !== 'pt-BR') {
                $override = require dirname(__DIR__, 2) . '/config/lang/' . $locale . '.php';
                if (is_array($override)) {
                    $base = array_merge($base, $override);
                }
            }
            self::$messages = $base;
            self::$loadedLocale = $locale;
        }

        $text = self::$messages[$key] ?? $key;

        foreach ($replace as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }
}
