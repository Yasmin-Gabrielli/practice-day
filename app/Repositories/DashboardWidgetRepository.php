<?php

declare(strict_types=1);

namespace App\Repositories;

final class DashboardWidgetRepository extends Repository
{
    /** @var list<string> */
    public const DEFAULT_TYPES = ['resumo', 'tarefas', 'disciplinas', 'arquivos', 'notas'];

    /** @return array<string, string> */
    public static function typeLabels(): array
    {
        return [
            'resumo' => 'Resumo dos estudos',
            'tarefas' => 'Próximas tarefas',
            'disciplinas' => 'Disciplinas',
            'arquivos' => 'Arquivos recentes',
            'notas' => 'Notas recentes',
        ];
    }

    public function ensureDefaultsForUser(string $userId): void
    {
        $statement = $this->db->prepare(
            'SELECT tipo_widget FROM widgets_dashboard WHERE usuario_id = :usuario_id'
        );
        $statement->execute(['usuario_id' => $userId]);
        $existing = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $existingTypes = is_array($existing) ? array_map('strval', $existing) : [];

        foreach (self::DEFAULT_TYPES as $type) {
            if (in_array($type, $existingTypes, true)) {
                continue;
            }

            $insert = $this->db->prepare(
                'INSERT INTO widgets_dashboard (id, usuario_id, tipo_widget, visivel)
                 VALUES (:id, :usuario_id, :tipo_widget, :visivel)'
            );
            $insert->execute([
                'id' => \App\Helpers\Uuid::v4(),
                'usuario_id' => $userId,
                'tipo_widget' => $type,
                'visivel' => 1,
            ]);
        }
    }

    /** @param array<string, mixed> $visibleById */
    public function syncVisibilityForUser(string $userId, array $visibleById): void
    {
        $widgets = $this->forUser($userId);
        $update = $this->db->prepare(
            'UPDATE widgets_dashboard SET visivel = :visivel WHERE id = :id AND usuario_id = :usuario_id'
        );

        foreach ($widgets as $widget) {
            $widgetId = (string) ($widget['id'] ?? '');
            if ($widgetId === '') {
                continue;
            }

            $update->execute([
                'visivel' => array_key_exists($widgetId, $visibleById) ? 1 : 0,
                'id' => $widgetId,
                'usuario_id' => $userId,
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    public function forUser(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT w.id, w.tipo_widget, w.visivel, p.posicao_x, p.posicao_y, p.largura, p.altura
             FROM widgets_dashboard w
             LEFT JOIN posicoes_widget p ON p.widget_id = w.id
             WHERE w.usuario_id = :usuario_id'
        );
        $statement->execute(['usuario_id' => $userId]);

        return $statement->fetchAll();
    }

    public function saveLayout(string $widgetId, string $userId, bool $visible, int $x, int $y, int $width, int $height): void
    {
        $widget = $this->db->prepare('UPDATE widgets_dashboard SET visivel = :visivel WHERE id = :id AND usuario_id = :usuario_id');
        $widget->execute(['visivel' => $visible, 'id' => $widgetId, 'usuario_id' => $userId]);

        $position = $this->db->prepare(
            'INSERT INTO posicoes_widget (id, widget_id, posicao_x, posicao_y, largura, altura)
             VALUES (:id, :widget_id, :x, :y, :largura, :altura)
             ON DUPLICATE KEY UPDATE posicao_x = VALUES(posicao_x), posicao_y = VALUES(posicao_y), largura = VALUES(largura), altura = VALUES(altura)'
        );
        $position->execute(['id' => \App\Helpers\Uuid::v4(), 'widget_id' => $widgetId, 'x' => $x, 'y' => $y, 'largura' => $width, 'altura' => $height]);
    }
}
