<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserSessionRepository extends Repository
{
    public function create(string $id, string $userId, string $tokenHash, string $ip, string $userAgent, string $expiresAt): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO sessoes_usuario (id, usuario_id, token_sessao, ip_origem, agente_usuario, expira_em)
             VALUES (:id, :usuario_id, :token_sessao, :ip_origem, :agente_usuario, :expira_em)'
        );
        $statement->execute([
            'id' => $id,
            'usuario_id' => $userId,
            'token_sessao' => $tokenHash,
            'ip_origem' => $ip,
            'agente_usuario' => $userAgent,
            'expira_em' => $expiresAt,
        ]);
    }

    public function isValid(string $userId, string $tokenHash): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM sessoes_usuario
             WHERE usuario_id = :usuario_id AND token_sessao = :token_sessao AND expira_em > NOW() LIMIT 1'
        );
        $statement->execute(['usuario_id' => $userId, 'token_sessao' => $tokenHash]);

        return $statement->fetchColumn() !== false;
    }

    public function invalidate(string $userId, string $tokenHash): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM sessoes_usuario WHERE usuario_id = :usuario_id AND token_sessao = :token_sessao'
        );
        $statement->execute(['usuario_id' => $userId, 'token_sessao' => $tokenHash]);
    }
}
