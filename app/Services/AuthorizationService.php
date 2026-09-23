<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuthorizationException;

/** Use esta guarda em serviços futuros antes de ler ou alterar recursos de um usuário. */
final class AuthorizationService extends Service
{
    public function assertOwnership(string $authenticatedUserId, string $resourceUserId): void
    {
        if (!hash_equals($authenticatedUserId, $resourceUserId)) {
            throw new AuthorizationException('Você não tem permissão para acessar este recurso.');
        }
    }
}
