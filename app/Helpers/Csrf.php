<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Exceptions\ValidationException;

final class Csrf
{
    public static function token(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /** @param array<string, mixed> $input */
    public static function validate(array $input): void
    {
        $provided = $input['_csrf_token'] ?? '';
        $stored = $_SESSION['_csrf_token'] ?? '';

        if (!is_string($provided) || !is_string($stored) || !hash_equals($stored, $provided)) {
            throw new ValidationException(['formulario' => 'A solicitação expirou. Atualize a página e tente novamente.']);
        }
    }
}
