<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserSettingsRepository extends Repository
{
    public function createDefaults(string $id, string $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO configuracoes_usuario (id, usuario_id, cor_primaria, cor_secundaria, idioma)
             VALUES (:id, :usuario_id, :cor_primaria, :cor_secundaria, :idioma)'
        );
        $statement->execute([
            'id' => $id,
            'usuario_id' => $userId,
            'cor_primaria' => '#0d6efd',
            'cor_secundaria' => '#ffffff',
            'idioma' => 'pt-BR',
        ]);
    }
}
