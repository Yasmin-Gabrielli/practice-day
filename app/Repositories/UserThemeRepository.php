<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserThemeRepository extends Repository
{
    public const PREFERENCES_THEME_NAME = 'preferencias';

    /** @return array<string, bool> */
    public function getNotificationPreferences(string $userId): array
    {
        $row = $this->findThemeRow($userId, self::PREFERENCES_THEME_NAME);
        if ($row === null) {
            return $this->defaultNotificationPreferences();
        }

        $decoded = json_decode((string) $row['propriedades_json'], true);
        if (!is_array($decoded)) {
            return $this->defaultNotificationPreferences();
        }

        return [
            'notificacoes_lembretes_tarefas' => !empty($decoded['notificacoes_lembretes_tarefas']),
            'notificacoes_eventos_calendario' => !empty($decoded['notificacoes_eventos_calendario']),
        ];
    }

    public function saveNotificationPreferences(string $userId, array $preferences): void
    {
        $payload = json_encode([
            'notificacoes_lembretes_tarefas' => !empty($preferences['notificacoes_lembretes_tarefas']),
            'notificacoes_eventos_calendario' => !empty($preferences['notificacoes_eventos_calendario']),
        ], JSON_THROW_ON_ERROR);

        $existing = $this->findThemeRow($userId, self::PREFERENCES_THEME_NAME);
        if ($existing !== null) {
            $statement = $this->db->prepare(
                'UPDATE temas_usuario SET propriedades_json = :json WHERE id = :id AND usuario_id = :usuario_id'
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
            'nome_tema' => self::PREFERENCES_THEME_NAME,
            'json' => $payload,
        ]);
    }

    public function createDefaultPreferences(string $userId): void
    {
        if ($this->findThemeRow($userId, self::PREFERENCES_THEME_NAME) !== null) {
            return;
        }

        $this->saveNotificationPreferences($userId, $this->defaultNotificationPreferences());
    }

    /** @return array<string, bool> */
    private function defaultNotificationPreferences(): array
    {
        return [
            'notificacoes_lembretes_tarefas' => true,
            'notificacoes_eventos_calendario' => true,
        ];
    }

    /** @return array<string, mixed>|null */
    private function findThemeRow(string $userId, string $themeName): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, propriedades_json FROM temas_usuario
             WHERE usuario_id = :usuario_id AND nome_tema = :nome_tema
             LIMIT 1'
        );
        $statement->execute(['usuario_id' => $userId, 'nome_tema' => $themeName]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
