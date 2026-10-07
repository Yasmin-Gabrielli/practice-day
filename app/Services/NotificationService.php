<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\NotificationStateRepository;
use App\Repositories\UserThemeRepository;

final class NotificationService extends Service
{
    private NotificationRepository $notifications;
    private NotificationStateRepository $state;
    private UserThemeRepository $themes;

    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}} $config */
    public function __construct(array $config)
    {
        $database = $config['database'];
        $this->notifications = new NotificationRepository($database);
        $this->state = new NotificationStateRepository($database);
        $this->themes = new UserThemeRepository($database);
    }

    /** @return array{items: list<array<string, mixed>>, pending_count: int, groups: array<string, list<array<string, mixed>>>} */
    public function feedForUser(string $userId, ?int $limit = null): array
    {
        $items = $this->buildItems($userId);
        usort($items, static function (array $a, array $b): int {
            $timeA = strtotime((string) ($a['occurred_at'] ?? '')) ?: 0;
            $timeB = strtotime((string) ($b['occurred_at'] ?? '')) ?: 0;

            return $timeA <=> $timeB;
        });

        if ($limit !== null && $limit > 0) {
            $items = array_slice($items, 0, $limit);
        }

        $pendingCount = $this->countPendingFromAll($userId);

        return [
            'items' => $items,
            'pending_count' => $pendingCount,
            'groups' => $this->groupItems($this->buildItems($userId)),
        ];
    }

    public function pendingCount(string $userId): int
    {
        return $this->countPendingFromAll($userId);
    }

    public function markAsViewed(string $userId, string $notificationKey): bool
    {
        if (str_starts_with($notificationKey, 'lembrete:')) {
            $reminderId = substr($notificationKey, strlen('lembrete:'));
            if ($reminderId !== '') {
                $this->notifications->markReminderFired($userId, $reminderId);
            }
        }

        $this->state->markViewed($userId, $notificationKey);

        return true;
    }

    public function markAllAsViewed(string $userId): void
    {
        $items = $this->buildItems($userId);
        $keys = [];
        foreach ($items as $item) {
            if (empty($item['read'])) {
                $keys[] = (string) $item['key'];
            }
        }

        foreach ($items as $item) {
            if (!empty($item['read'])) {
                continue;
            }
            $key = (string) ($item['key'] ?? '');
            if (str_starts_with($key, 'lembrete:')) {
                $reminderId = substr($key, strlen('lembrete:'));
                if ($reminderId !== '') {
                    $this->notifications->markReminderFired($userId, $reminderId);
                }
            }
        }

        $this->state->markManyViewed($userId, $keys);
    }

    /** @return list<array<string, mixed>> */
    private function buildItems(string $userId): array
    {
        $preferences = $this->themes->getNotificationPreferences($userId);
        $viewed = $this->state->viewedKeys($userId);
        $items = [];

        if (!empty($preferences['notificacoes_lembretes_tarefas'])) {
            foreach ($this->notifications->overdueTasks($userId) as $task) {
                $items[] = $this->mapTask($task, 'tarefa_vencida', 'Tarefa vencida', 'bi-exclamation-circle', $viewed);
            }
            foreach ($this->notifications->upcomingTasks($userId) as $task) {
                $items[] = $this->mapTask($task, 'tarefa_proxima', 'Vencimento próximo', 'bi-calendar-event', $viewed);
            }
        }

        if (!empty($preferences['notificacoes_eventos_calendario'])) {
            foreach ($this->notifications->upcomingEvents($userId) as $event) {
                $items[] = $this->mapEvent($event, $viewed);
            }
        }

        foreach ($this->notifications->pendingReminders($userId) as $reminder) {
            $origin = (string) ($reminder['origem'] ?? 'evento');
            if ($origin === 'tarefa' && empty($preferences['notificacoes_lembretes_tarefas'])) {
                continue;
            }
            if ($origin === 'evento' && empty($preferences['notificacoes_eventos_calendario'])) {
                continue;
            }
            $items[] = $this->mapReminder($reminder, $viewed);
        }

        return $items;
    }

    /** @param array<string, mixed> $task @param array<string, string> $viewed */
    private function mapTask(array $task, string $type, string $label, string $icon, array $viewed): array
    {
        $id = (string) ($task['id'] ?? '');
        $key = $type . ':' . $id;
        $dueAt = (string) ($task['data_vencimento'] ?? '');

        return [
            'key' => $key,
            'type' => $type,
            'category' => $type === 'tarefa_vencida' ? 'tarefas_vencidas' : 'tarefas_proximas',
            'label' => $label,
            'title' => (string) ($task['titulo'] ?? 'Tarefa'),
            'subtitle' => (string) ($task['disciplina_nome'] ?? 'Sem disciplina'),
            'occurred_at' => $dueAt,
            'url' => '/planner?tarefa=' . rawurlencode($id),
            'icon' => $icon,
            'read' => isset($viewed[$key]),
        ];
    }

    /** @param array<string, mixed> $event @param array<string, string> $viewed */
    private function mapEvent(array $event, array $viewed): array
    {
        $id = (string) ($event['id'] ?? '');
        $key = 'evento:' . $id;

        return [
            'key' => $key,
            'type' => 'evento',
            'category' => 'eventos',
            'label' => 'Evento próximo',
            'title' => (string) ($event['titulo'] ?? 'Evento'),
            'subtitle' => $this->eventTypeLabel((string) ($event['tipo'] ?? 'evento')),
            'occurred_at' => (string) ($event['data_inicio'] ?? ''),
            'url' => '/calendario?evento=' . rawurlencode($id),
            'icon' => 'bi-calendar3',
            'read' => isset($viewed[$key]),
        ];
    }

    /** @param array<string, mixed> $reminder @param array<string, string> $viewed */
    private function mapReminder(array $reminder, array $viewed): array
    {
        $id = (string) ($reminder['id'] ?? '');
        $key = 'lembrete:' . $id;
        $origin = (string) ($reminder['origem'] ?? 'evento');
        $relatedId = $origin === 'tarefa'
            ? (string) ($reminder['tarefa_id'] ?? '')
            : (string) ($reminder['evento_id'] ?? '');
        if ($relatedId === '') {
            $url = '/notificacoes';
        } elseif ($origin === 'tarefa') {
            $url = '/planner?tarefa=' . rawurlencode($relatedId);
        } else {
            $url = '/calendario?evento=' . rawurlencode($relatedId);
        }
        $due = (string) ($reminder['data_hora_lembrete'] ?? '');
        $isDue = $due !== '' && strtotime($due) <= time();

        return [
            'key' => $key,
            'type' => 'lembrete',
            'category' => 'lembretes',
            'label' => $isDue ? 'Lembrete pendente' : 'Lembrete agendado',
            'title' => (string) ($reminder['titulo'] ?? 'Lembrete'),
            'subtitle' => $origin === 'tarefa' ? 'Lembrete de tarefa' : 'Lembrete de evento',
            'occurred_at' => $due,
            'url' => $url,
            'icon' => 'bi-bell',
            'read' => !empty($reminder['disparado']) || isset($viewed[$key]),
        ];
    }

    private function eventTypeLabel(string $type): string
    {
        return match ($type) {
            'prova' => 'Prova',
            'trabalho' => 'Trabalho',
            'lembrete' => 'Lembrete',
            default => 'Evento',
        };
    }

    /** @param list<array<string, mixed>> $items @return array<string, list<array<string, mixed>>> */
    private function groupItems(array $items): array
    {
        $groups = [
            'tarefas_vencidas' => [],
            'tarefas_proximas' => [],
            'eventos' => [],
            'lembretes' => [],
        ];

        foreach ($items as $item) {
            $category = (string) ($item['category'] ?? '');
            if (isset($groups[$category])) {
                $groups[$category][] = $item;
            }
        }

        return $groups;
    }

    private function countPendingFromAll(string $userId): int
    {
        $count = 0;
        foreach ($this->buildItems($userId) as $item) {
            if (empty($item['read'])) {
                $count++;
            }
        }

        return $count;
    }
}
