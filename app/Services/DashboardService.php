<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\Repositories\DashboardWidgetRepository;

final class DashboardService extends Service
{
    private DashboardRepository $dashboard;
    private DashboardWidgetRepository $widgets;

    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}} $config */
    public function __construct(array $config)
    {
        $this->dashboard = new DashboardRepository($config['database']);
        $this->widgets = new DashboardWidgetRepository($config['database']);
    }

    /** @return array<string, mixed> */
    public function forUser(string $userId): array
    {
        $this->widgets->ensureDefaultsForUser($userId);
        $widgets = $this->widgetConfiguration($this->widgets->forUser($userId));

        return [
            'summary' => [
                ...$this->dashboard->taskSummary($userId),
                'studied_minutes' => $this->dashboard->studiedMinutes($userId),
                'productivity' => $this->dashboard->productivityAverage($userId),
            ],
            'tasks' => $this->dashboard->upcomingTasks($userId),
            'files' => $this->dashboard->recentFiles($userId),
            'notes' => $this->dashboard->recentNotes($userId),
            'disciplines' => $this->dashboard->disciplines($userId),
            'widgets' => $widgets,
        ];
    }

    /** @param list<array<string, mixed>> $rows @return array<string, array<string, mixed>> */
    private function widgetConfiguration(array $rows): array
    {
        $configuration = [];

        foreach ($rows as $row) {
            $type = $row['tipo_widget'] ?? null;
            if (!is_string($type)) {
                continue;
            }
            $configuration[$type] = [
                'id' => $row['id'],
                'visible' => (bool) $row['visivel'],
                'x' => $row['posicao_x'],
                'y' => $row['posicao_y'],
                'width' => $row['largura'],
                'height' => $row['altura'],
            ];
        }

        return $configuration;
    }
}
