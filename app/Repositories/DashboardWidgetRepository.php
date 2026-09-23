<?php

declare(strict_types=1);

namespace App\Repositories;

final class DashboardWidgetRepository extends Repository
{
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
