<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\AuthenticationException;
use App\Services\AuthenticationService;

/** Ponto de extensão para proteger rotas quando o login for implementado. */
final class AuthenticationMiddleware implements Middleware
{
    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}, auth: array{session_lifetime_minutes: int}} $config */
    public function __construct(private readonly array $config)
    {
    }

    public function handle(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $token = $_SESSION['session_token'] ?? null;

        if (!is_string($userId) || !is_string($token) || !(new AuthenticationService($this->config))->isSessionValid($userId, $token)) {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_avatar'], $_SESSION['session_token'], $_SESSION['session_expires_at']);
            throw new AuthenticationException('Autenticação necessária.');
        }
    }
}
