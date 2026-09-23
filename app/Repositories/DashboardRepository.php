<?php

declare(strict_types=1);

namespace App\Repositories;

final class DashboardRepository extends Repository
{
    /** @return array{pending: int, completed: int} */
    public function taskSummary(string $userId): array
    {
        $statement = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN status <> 'concluido' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) AS completed
             FROM tarefas WHERE usuario_id = :usuario_id"
        );
        $statement->execute(['usuario_id' => $userId]);
        $summary = $statement->fetch() ?: [];

        return ['pending' => (int) ($summary['pending'] ?? 0), 'completed' => (int) ($summary['completed'] ?? 0)];
    }

    public function studiedMinutes(string $userId): ?int
    {
        $statement = $this->db->prepare(
            'SELECT SUM(duracao_minutos) FROM sessoes_estudo WHERE usuario_id = :usuario_id AND duracao_minutos IS NOT NULL'
        );
        $statement->execute(['usuario_id' => $userId]);
        $minutes = $statement->fetchColumn();

        return $minutes === null ? null : (int) $minutes;
    }

    public function productivityAverage(string $userId): ?float
    {
        $statement = $this->db->prepare(
            'SELECT AVG(pontuacao_produtividade) FROM registros_produtividade WHERE usuario_id = :usuario_id'
        );
        $statement->execute(['usuario_id' => $userId]);
        $score = $statement->fetchColumn();

        return $score === null ? null : (float) $score;
    }

    /** @return list<array<string, mixed>> */
    public function upcomingTasks(string $userId): array
    {
        $statement = $this->db->prepare(
            "SELECT t.titulo, t.prioridade, t.status, t.data_vencimento, d.nome AS disciplina_nome
             FROM tarefas t
             LEFT JOIN disciplinas d ON d.id = t.disciplina_id AND d.usuario_id = t.usuario_id
             WHERE t.usuario_id = :usuario_id AND t.status <> 'concluido'
             ORDER BY t.data_vencimento IS NULL, t.data_vencimento ASC, t.criado_em DESC LIMIT 5"
        );
        $statement->execute(['usuario_id' => $userId]);

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function recentFiles(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT nome_original, nome_arquivo, extensao, tipo_mime, criado_em
             FROM arquivos WHERE usuario_id = :usuario_id AND excluido = FALSE
             ORDER BY criado_em DESC LIMIT 4'
        );
        $statement->execute(['usuario_id' => $userId]);

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function recentNotes(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT n.titulo, n.atualizado_em, d.nome AS disciplina_nome
             FROM notas n
             LEFT JOIN disciplinas d ON d.id = n.disciplina_id AND d.usuario_id = n.usuario_id
             WHERE n.usuario_id = :usuario_id
             ORDER BY n.atualizado_em DESC LIMIT 4'
        );
        $statement->execute(['usuario_id' => $userId]);

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function disciplines(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT nome, cor, icone FROM disciplinas WHERE usuario_id = :usuario_id ORDER BY nome ASC LIMIT 8'
        );
        $statement->execute(['usuario_id' => $userId]);

        return $statement->fetchAll();
    }
}
