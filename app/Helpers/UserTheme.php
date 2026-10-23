<?php

declare(strict_types=1);

namespace App\Helpers;

final class UserTheme
{
    /** @return array<string, mixed> */
    public static function settings(): array
    {
        $settings = $_SESSION['user_settings'] ?? null;

        return is_array($settings) ? $settings : [];
    }

    public static function isDarkMode(): bool
    {
        return (int) (self::settings()['modo_escuro'] ?? 0) === 1;
    }

    public static function htmlLang(): string
    {
        $language = (string) (self::settings()['idioma'] ?? 'pt-BR');

        return htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
    }

    public static function htmlAttributes(): string
    {
        $attributes = 'lang="' . self::htmlLang() . '"';
        if (self::isDarkMode()) {
            $attributes .= ' data-bs-theme="dark"';
        }

        return $attributes;
    }

    /** @return list<string> */
    public static function bodyClasses(): array
    {
        $classes = ['app-body'];
        $fontSize = (string) (self::settings()['tamanho_fonte'] ?? 'médio');
        $classes[] = match ($fontSize) {
            'pequeno' => 'app-font-sm',
            'grande' => 'app-font-lg',
            default => 'app-font-md',
        };

        if ((int) (self::settings()['animacoes_ativas'] ?? 1) !== 1) {
            $classes[] = 'app-reduced-motion';
        }

        $wallpaper = trim((string) (self::settings()['papel_parede'] ?? ''));
        if ($wallpaper !== '') {
            $classes[] = 'app-has-wallpaper';
        }

        return $classes;
    }

    public static function bodyStyle(): string
    {
        $settings = self::settings();
        $primary = self::sanitizeHex((string) ($settings['cor_primaria'] ?? '#2563eb'), '#2563eb');
        $secondary = self::sanitizeHex((string) ($settings['cor_secundaria'] ?? '#ffffff'), '#ffffff');
        $primaryRgb = self::hexToRgb($primary);
        $wallpaper = trim((string) ($settings['papel_parede'] ?? ''));

        $styles = [
            '--bs-primary:' . $primary,
            '--bs-primary-rgb:' . implode(', ', $primaryRgb),
            '--app-accent-secondary:' . $secondary,
        ];

        if ($wallpaper !== '' && self::isSafeImageUrl($wallpaper)) {
            $safeUrl = htmlspecialchars($wallpaper, ENT_QUOTES, 'UTF-8');
            $styles[] = '--app-wallpaper:url("' . $safeUrl . '")';
        }

        // Cor de texto com contraste automatico para a cor primaria
        $styles[] = '--bs-primary-text:' . self::primaryTextColor($primary);

        return implode(';', $styles);
    }

    private static function isSafeImageUrl(string $value): bool
    {
        if (str_starts_with($value, '/')) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $value) === 1;
    }

    private static function sanitizeHex(string $value, string $fallback): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
    }

    /**
     * Calcula a cor de texto (preto ou branco) com melhor contraste
     * para a cor primaria informada, com base na luminancia relativa.
     */
    public static function primaryTextColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        // Luminancia relativa (sRGB)
        $lum = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        return $lum > 0.55 ? '#111827' : '#ffffff';
    }

    /** @return array{0: int, 1: int, 2: int} */
    public static function hexToRgbArray(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
