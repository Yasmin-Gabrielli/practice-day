<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class NotificationRepository extends Repository
{
    private const UPCOMING_TASK_DAYS = 7;
    private const UPCOMING_EVENT_DAYS = 30;
    private const LEMBRETE_LOOKAHEAD_DAYS = 7;

    /** @return list<array<string, mixed>> */
    public function overdueTasks(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT t.id, t.titulo, t.data_vencimento, t.prioridade, t.status,
                    d.nome AS disciplina_nome
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.usuario_id = :usuario_id
               AND t.status <> :concluido
               AND t.data_vencimento IS NOT NULL
               AND t.data_vencimento < NOW()
             ORDER BY t.data_vencimento ASC'
        );
        $statement->execute([
            'usuario_id' => $userId,
            'concluido' => 'concluido',
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string, mixed>> */
    public function upcomingTasks(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT t.id, t.titulo, t.data_vencimento, t.prioridade, t.status,
                    d.nome AS disciplina_nome
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.usuario_id = :usuario_id
               AND t.status <> :concluido
               AND t.data_vencimento IS NOT NULL
               AND t.data_vencimento >= NOW()
               AND t.data_vencimento <= DATE_ADD(NOW(), INTERVAL :days DAY)
             ORDER BY t.data_vencimento ASC'
        );
        $statement->execute([
            'usuario_id' => $userId,
            'concluido' => 'concluido',
            'days' => self::UPCOMING_TASK_DAYS,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string, mixed>> */
    public function upcomingEvents(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT e.id, e.titulo, e.data_inicio, e.data_fim, e.tipo,
                    d.nome AS disciplina_nome
             FROM eventos_calendario e
             LEFT JOIN disciplinas d ON e.disciplina_id = d.id
             WHERE e.usuario_id = :usuario_id
               AND e.data_inicio >= NOW()
               AND e.data_inicio <= DATE_ADD(NOW(), INTERVAL :days DAY)
             ORDER BY e.data_inicio ASC'
        );
        $statement->execute([
            'usuario_id' => $userId,
            'days' => self::UPCOMING_EVENT_DAYS,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string, mixed>> */
    public function pendingReminders(string $userId): array
    {
        // PDO named parameters cannot appear more than once in a query.
        // We use :uid_ev and :uid_tk as separate bindings for the same value.
        $statement = $this->db->prepare(
            'SELECT l.id, l.evento_id, l.tarefa_id, l.data_hora_lembrete, l.disparado,
                    COALESCE(e.titulo, t.titulo) AS titulo,
                    COALESCE(e.usuario_id, t.usuario_id) AS usuario_id,
                    CASE
                        WHEN l.evento_id IS NOT NULL THEN :tipo_evento
                        ELSE :tipo_tarefa
                    END AS origem
             FROM lembretes l
             LEFT JOIN eventos_calendario e ON l.evento_id = e.id
             LEFT JOIN tarefas t ON l.tarefa_id = t.id
             WHERE (e.usuario_id = :uid_ev OR t.usuario_id = :uid_tk)
               AND l.disparado = FALSE
               AND l.data_hora_lembrete <= DATE_ADD(NOW(), INTERVAL :days DAY)
             ORDER BY l.data_hora_lembrete ASC'
        );
        $statement->execute([
            'uid_ev'     => $userId,
            'uid_tk'     => $userId,
            'tipo_evento' => 'evento',
            'tipo_tarefa' => 'tarefa',
            'days'       => self::LEMBRETE_LOOKAHEAD_DAYS,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markReminderFired(string $userId, string $reminderId): bool
    {
        // Split :usuario_id into two distinct params to avoid HY093.
        $statement = $this->db->prepare(
            'UPDATE lembretes l
             LEFT JOIN eventos_calendario e ON l.evento_id = e.id
             LEFT JOIN tarefas t ON l.tarefa_id = t.id
             SET l.disparado = TRUE
             WHERE l.id = :id AND (e.usuario_id = :uid_ev OR t.usuario_id = :uid_tk)'
        );
        $statement->execute([
            'id'    => $reminderId,
            'uid_ev' => $userId,
            'uid_tk' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }
}
