<?php

declare(strict_types=1);

namespace App\Repositories;

final class LoginAttemptRepository extends Repository
{
    public function record(string $id, string $email, string $ipAddress, bool $success): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO tentativas_login (id, email, endereco_ip, sucesso) VALUES (:id, :email, :endereco_ip, :sucesso)'
        );
        $statement->execute([
            'id' => $id,
            'email' => $email,
            'endereco_ip' => $ipAddress,
            'sucesso' => $success,
        ]);
    }
}
