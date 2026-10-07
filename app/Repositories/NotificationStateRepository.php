<?php

declare(strict_types=1);

namespace App\Repositories;

final class NotificationStateRepository extends Repository
{
    public const STATE_THEME_NAME = 'notificacoes_visualizadas';

    /** @return array<string, string> */
    public function viewedKeys(string $userId): array
    {
        $row = $this->findRow($userId);
        if ($row === null) {
            return [];
        }

        $decoded = json_decode((string) $row['propriedades_json'], true);
        if (!is_array($decoded) || !is_array($decoded['visualizadas'] ?? null)) {
            return [];
        }

        $viewed = [];
        foreach ($decoded['visualizadas'] as $key => $timestamp) {
            if (is_string($key) && is_string($timestamp)) {
                $viewed[$key] = $timestamp;
            }
        }

        return $viewed;
    }

    public function markViewed(string $userId, string $notificationKey): void
    {
        $viewed = $this->viewedKeys($userId);
        $viewed[$notificationKey] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->persist($userId, $viewed);
    }

    /** @param list<string> $notificationKeys */
    public function markManyViewed(string $userId, array $notificationKeys): void
    {
        $viewed = $this->viewedKeys($userId);
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        foreach ($notificationKeys as $key) {
            if ($key !== '') {
                $viewed[$key] = $now;
            }
        }
        $this->persist($userId, $viewed);
    }

    /** @param array<string, string> $viewed */
    private function persist(string $userId, array $viewed): void
    {
        $payload = json_encode(['visualizadas' => $viewed], JSON_THROW_ON_ERROR);
        $existing = $this->findRow($userId);

        if ($existing !== null) {
            $statement = $this->db->prepare(
                'UPDATE temas_usuario SET propriedades_json = :json
                 WHERE id = :id AND usuario_id = :usuario_id'
            );
            $statement->execute([
                'json' => $payload,
                'id' => $existing['id'],
                'usuario_id' => $userId,
            ]);

            return;
        }

        $statement = $this->db->prepare(
            'INSERT INTO temas_usuario (id, usuario_id, nome_tema, propriedades_json)
             VALUES (:id, :usuario_id, :nome_tema, :json)'
        );
        $statement->execute([
            'id' => \App\Helpers\Uuid::v4(),
            'usuario_id' => $userId,
            'nome_tema' => self::STATE_THEME_NAME,
            'json' => $payload,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function findRow(string $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, propriedades_json FROM temas_usuario
             WHERE usuario_id = :usuario_id AND nome_tema = :nome_tema
             LIMIT 1'
        );
        $statement->execute([
            'usuario_id' => $userId,
            'nome_tema' => self::STATE_THEME_NAME,
        ]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
