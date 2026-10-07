<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends Repository
{
    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, nome, email, senha_hash, avatar FROM usuarios WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function existsByEmail(string $email): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM usuarios WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetchColumn() !== false;
    }

    public function create(string $id, string $name, string $email, string $passwordHash): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO usuarios (id, nome, email, senha_hash) VALUES (:id, :nome, :email, :senha_hash)'
        );
        $statement->execute(['id' => $id, 'nome' => $name, 'email' => $email, 'senha_hash' => $passwordHash]);
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nome, email, avatar FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function updateProfile(string $id, string $name, string $email, ?string $avatar): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET nome = :nome, email = :email, avatar = :avatar WHERE id = :id');
        $stmt->execute(['id' => $id, 'nome' => $name, 'email' => $email, 'avatar' => $avatar]);
    }
}
