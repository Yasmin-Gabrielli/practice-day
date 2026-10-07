<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserSettingsRepository extends Repository
{
    public function createDefaults(string $id, string $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO configuracoes_usuario (id, usuario_id, modo_escuro, cor_primaria, cor_secundaria, tamanho_fonte, animacoes_ativas, idioma)
             VALUES (:id, :usuario_id, :modo_escuro, :cor_primaria, :cor_secundaria, :tamanho_fonte, :animacoes_ativas, :idioma)'
        );
        $statement->execute([
            'id' => $id,
            'usuario_id' => $userId,
            'modo_escuro' => 0,
            'cor_primaria' => '#2563eb',
            'cor_secundaria' => '#ffffff',
            'tamanho_fonte' => 'médio',
            'animacoes_ativas' => 1,
            'idioma' => 'pt-BR',
        ]);
    }

    public function ensureForUser(string $userId): void
    {
        $statement = $this->db->prepare(
            'SELECT id FROM configuracoes_usuario WHERE usuario_id = :uid LIMIT 1'
        );
        $statement->execute(['uid' => $userId]);
        if ($statement->fetchColumn() === false) {
            $this->createDefaults(\App\Helpers\Uuid::v4(), $userId);
        }
    }

    /** @return array<string, mixed> */
    public function getForUser(string $userId): array
    {
        $this->ensureForUser($userId);

        $stmt = $this->db->prepare('SELECT * FROM configuracoes_usuario WHERE usuario_id = :uid LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($settings) ? $settings : [];
    }

    /** @param array<string, mixed> $data */
    public function updateForUser(string $userId, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE configuracoes_usuario SET
                modo_escuro = :modo_escuro,
                cor_primaria = :cor_primaria,
                cor_secundaria = :cor_secundaria,
                papel_parede = :papel_parede,
                tamanho_fonte = :tamanho_fonte,
                animacoes_ativas = :animacoes_ativas,
                idioma = :idioma
            WHERE usuario_id = :uid
        ');
        $stmt->execute([
            'modo_escuro' => $data['modo_escuro'],
            'cor_primaria' => $data['cor_primaria'],
            'cor_secundaria' => $data['cor_secundaria'],
            'papel_parede' => $data['papel_parede'],
            'tamanho_fonte' => $data['tamanho_fonte'],
            'animacoes_ativas' => $data['animacoes_ativas'],
            'idioma' => $data['idioma'],
            'uid' => $userId,
        ]);
    }
}
