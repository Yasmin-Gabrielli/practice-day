<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\AuthorizationException;

/** Ponto de extensão para verificar permissões após a autenticação. */
final class AuthorizationMiddleware implements Middleware
{
    /** @param list<string> $allowedRoles */
    public function __construct(private readonly array $allowedRoles)
    {
    }

    public function handle(): void
    {
        $role = $_SESSION['user_role'] ?? null;

        if (!is_string($role) || !in_array($role, $this->allowedRoles, true)) {
            throw new AuthorizationException('Você não tem permissão para acessar este recurso.');
        }
    }
}
