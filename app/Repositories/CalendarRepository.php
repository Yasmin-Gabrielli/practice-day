<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CalendarRepository extends Repository
{
    public function getEvents(string $userId, string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM eventos_calendario e
             LEFT JOIN disciplinas d ON e.disciplina_id = d.id
             WHERE e.usuario_id = :u AND e.data_inicio < :end AND e.data_fim >= :start
             ORDER BY e.data_inicio ASC'
        );
        $stmt->execute(['u' => $userId, 'start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksAsEvents(string $userId, string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.usuario_id = :u AND t.data_vencimento IS NOT NULL 
               AND t.data_vencimento >= :start AND t.data_vencimento < :end
             ORDER BY t.data_vencimento ASC'
        );
        $stmt->execute(['u' => $userId, 'start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findTaskAsEvent(string $userId, string $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.id, t.titulo, t.descricao, t.data_vencimento AS data_inicio,
                    t.data_vencimento AS data_fim, 'tarefa' AS tipo, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.id = :id AND t.usuario_id = :u"
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function getUpcomingEvents(string $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.id, e.titulo, e.data_inicio, e.tipo, d.cor AS disciplina_cor
             FROM eventos_calendario e
             LEFT JOIN disciplinas d ON e.disciplina_id = d.id
             WHERE e.usuario_id = :u AND e.data_inicio >= NOW()
             ORDER BY e.data_inicio ASC LIMIT ' . (int)$limit
        );
        $stmt->execute(['u' => $userId]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare(
            "SELECT t.id, t.titulo, t.data_vencimento AS data_inicio, 'tarefa' AS tipo, d.cor AS disciplina_cor
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.usuario_id = :u AND t.data_vencimento IS NOT NULL AND t.data_vencimento >= NOW()
             ORDER BY t.data_vencimento ASC LIMIT " . (int)$limit
        );
        $stmt->execute(['u' => $userId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($events, $tasks);
        usort($merged, fn($a, $b) => $a['data_inicio'] <=> $b['data_inicio']);
        return array_slice($merged, 0, $limit);
    }

    public function findEvent(string $userId, string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM eventos_calendario e
             LEFT JOIN disciplinas d ON e.disciplina_id = d.id
             WHERE e.id = :id AND e.usuario_id = :u'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $stmt = $this->db->prepare('SELECT * FROM lembretes WHERE evento_id = :e ORDER BY data_hora_lembrete ASC');
        $stmt->execute(['e' => $id]);
        $row['lembretes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function createEvent(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO eventos_calendario (id, usuario_id, disciplina_id, titulo, descricao, data_inicio, data_fim, tipo)
             VALUES (:id, :u, :d, :t, :desc, :di, :df, :tp)'
        );
        $stmt->execute($data);
    }

    public function updateEvent(array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE eventos_calendario 
             SET disciplina_id = :d, titulo = :t, descricao = :desc, data_inicio = :di, data_fim = :df, tipo = :tp
             WHERE id = :id AND usuario_id = :u'
        );
        $stmt->execute($data);
    }

    public function deleteEvent(string $userId, string $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM eventos_calendario WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
    }

    public function addReminder(string $id, string $eventId, string $datetime): void
    {
        $stmt = $this->db->prepare('INSERT INTO lembretes (id, evento_id, data_hora_lembrete) VALUES (:id, :e, :dt)');
        $stmt->execute(['id' => $id, 'e' => $eventId, 'dt' => $datetime]);
    }

    public function deleteRemindersByEvent(string $eventId): void
    {
        $stmt = $this->db->prepare('DELETE FROM lembretes WHERE evento_id = :e');
        $stmt->execute(['e' => $eventId]);
    }
}
