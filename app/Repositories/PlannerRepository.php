<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use App\Helpers\Uuid;

final class PlannerRepository extends Repository
{
    public function list(string $userId, ?string $status = null, ?string $priority = null, ?string $disciplineId = null): array
    {
        $sql = 'SELECT t.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor,
                    (SELECT COUNT(*) FROM listas_checagem_tarefa lct WHERE lct.tarefa_id = t.id) as total_checklist,
                    (SELECT COUNT(*) FROM listas_checagem_tarefa lct WHERE lct.tarefa_id = t.id AND lct.concluido = 1) as concluidos_checklist,
                    (SELECT COUNT(*) FROM anexos_tarefa a WHERE a.tarefa_id = t.id) as total_anexos
                FROM tarefas t
                LEFT JOIN disciplinas d ON t.disciplina_id = d.id
                WHERE t.usuario_id = :u';
        $params = ['u' => $userId];

        if ($status !== null && $status !== '') {
            $sql .= ' AND t.status = :s';
            $params['s'] = $status;
        }
        if ($priority !== null && $priority !== '') {
            $sql .= ' AND t.prioridade = :p';
            $params['p'] = $priority;
        }
        if ($disciplineId !== null && $disciplineId !== '') {
            $sql .= ' AND t.disciplina_id = :d';
            $params['d'] = $disciplineId;
        }

        $sql .= ' ORDER BY t.data_vencimento ASC, t.criado_em DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $userId, string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM tarefas t
             LEFT JOIN disciplinas d ON t.disciplina_id = d.id
             WHERE t.id = :id AND t.usuario_id = :u'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $stmt = $this->db->prepare('SELECT * FROM listas_checagem_tarefa WHERE tarefa_id = :t ORDER BY id ASC');
        $stmt->execute(['t' => $id]);
        $row['checklist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare(
            'SELECT a.id, a.arquivo_id, f.nome_original, f.extensao, f.tamanho_bytes 
             FROM anexos_tarefa a 
             JOIN arquivos f ON a.arquivo_id = f.id 
             WHERE a.tarefa_id = :t'
        );
        $stmt->execute(['t' => $id]);
        $row['anexos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tarefas (id, usuario_id, disciplina_id, titulo, descricao, prioridade, status, data_vencimento, recorrente)
             VALUES (:id, :u, :d, :t, :desc, :p, :s, :dt, :r)'
        );
        $stmt->execute($data);
    }

    public function update(array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tarefas 
             SET disciplina_id = :d, titulo = :t, descricao = :desc, prioridade = :p, status = :s, data_vencimento = :dt, recorrente = :r
             WHERE id = :id AND usuario_id = :u'
        );
        $stmt->execute($data);
    }

    public function delete(string $userId, string $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tarefas WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
    }

    public function updateStatus(string $userId, string $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE tarefas SET status = :s WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['s' => $status, 'id' => $id, 'u' => $userId]);
    }

    public function addChecklistItem(string $taskId, string $description): void
    {
        $stmt = $this->db->prepare('INSERT INTO listas_checagem_tarefa (id, tarefa_id, descricao, concluido) VALUES (:id, :t, :d, 0)');
        $stmt->execute(['id' => Uuid::v4(), 't' => $taskId, 'd' => $description]);
    }

    public function toggleChecklistItem(string $taskId, string $itemId, int $concluido): void
    {
        $stmt = $this->db->prepare('UPDATE listas_checagem_tarefa SET concluido = :c WHERE id = :id AND tarefa_id = :t');
        $stmt->execute(['c' => $concluido, 'id' => $itemId, 't' => $taskId]);
    }

    public function deleteChecklistItem(string $taskId, string $itemId): void
    {
        $stmt = $this->db->prepare('DELETE FROM listas_checagem_tarefa WHERE id = :id AND tarefa_id = :t');
        $stmt->execute(['id' => $itemId, 't' => $taskId]);
    }

    public function addAttachment(string $taskId, string $fileId): void
    {
        $stmt = $this->db->prepare('INSERT INTO anexos_tarefa (id, tarefa_id, arquivo_id) VALUES (:id, :t, :a)');
        $stmt->execute(['id' => Uuid::v4(), 't' => $taskId, 'a' => $fileId]);
    }

    public function removeAttachment(string $taskId, string $attachmentId): void
    {
        $stmt = $this->db->prepare('DELETE FROM anexos_tarefa WHERE id = :id AND tarefa_id = :t');
        $stmt->execute(['id' => $attachmentId, 't' => $taskId]);
    }
}
