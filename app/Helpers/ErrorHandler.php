<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    /** @param array<string, mixed> $config */
    public static function register(array $config): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
        set_error_handler(static function (int $severity, string $message, string $file, int $line) {
            if ((error_reporting() & $severity) === 0) {
                return false; // Erros suprimidos com @ continuam silenciosos.
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /** @param array<string, mixed> $config */
    public static function handleException(Throwable $exception, array $config): never
    {
        error_log((string) $exception);

        $status = match (true) {
            $exception instanceof ValidationException => 422,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof NotFoundException => 404,
            default => 500,
        };

        if (self::expectsJson()) {
            $response = ['message' => $status === 500 ? 'Erro interno do servidor.' : $exception->getMessage()];

            if ($exception instanceof ValidationException) {
                $response['errors'] = $exception->errors();
            }

            if ($config['app']['debug']) {
                $response['debug'] = $exception->getMessage();
            }

            JsonResponse::send($response, $status);
        }

        http_response_code($status);

        if ($config['app']['debug']) {
            echo '<pre>Erro interno: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            echo 'Ocorreu um erro interno. Tente novamente mais tarde.';
        }

        exit;
    }

    private static function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

        return str_contains($accept, 'application/json') || str_starts_with($path, '/api/');
    }
}
