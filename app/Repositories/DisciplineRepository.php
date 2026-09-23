<?php

declare(strict_types=1);

namespace App\Repositories;

final class DisciplineRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function allForUser(string $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT d.*, (SELECT COUNT(*) FROM arquivos a WHERE a.disciplina_id = d.id AND a.usuario_id = d.usuario_id AND a.excluido = FALSE) AS arquivos_count, (SELECT COUNT(*) FROM tarefas t WHERE t.disciplina_id = d.id AND t.usuario_id = d.usuario_id) AS tarefas_count, (SELECT COUNT(*) FROM notas n WHERE n.disciplina_id = d.id AND n.usuario_id = d.usuario_id) AS notas_count FROM disciplinas d WHERE d.usuario_id = :usuario_id ORDER BY d.nome ASC'
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findForUser(string $id, string $userId): ?array
    {
        $statement = $this->db->prepare(
            "SELECT d.*, (SELECT COUNT(*) FROM arquivos a WHERE a.disciplina_id = d.id AND a.usuario_id = d.usuario_id AND a.excluido = FALSE) AS arquivos_count, (SELECT COUNT(*) FROM tarefas t WHERE t.disciplina_id = d.id AND t.usuario_id = d.usuario_id) AS tarefas_count, (SELECT COUNT(*) FROM tarefas t WHERE t.disciplina_id = d.id AND t.usuario_id = d.usuario_id AND t.status = 'concluido') AS tarefas_concluidas_count, (SELECT COUNT(*) FROM notas n WHERE n.disciplina_id = d.id AND n.usuario_id = d.usuario_id) AS notas_count, (SELECT SUM(s.duracao_minutos) FROM sessoes_estudo s WHERE s.disciplina_id = d.id AND s.usuario_id = d.usuario_id AND s.duracao_minutos IS NOT NULL) AS minutos_estudados FROM disciplinas d WHERE d.id = :id AND d.usuario_id = :usuario_id LIMIT 1"
        );
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        $discipline = $statement->fetch();
        return is_array($discipline) ? $discipline : null;
    }

    public function create(string $id, string $userId, string $name, string $color, string $icon): void
    {
        $statement = $this->db->prepare('INSERT INTO disciplinas (id, usuario_id, nome, cor, icone) VALUES (:id, :usuario_id, :nome, :cor, :icone)');
        $statement->execute(['id' => $id, 'usuario_id' => $userId, 'nome' => $name, 'cor' => $color, 'icone' => $icon]);
    }

    public function update(string $id, string $userId, string $name, string $color, string $icon): bool
    {
        $statement = $this->db->prepare('UPDATE disciplinas SET nome = :nome, cor = :cor, icone = :icone WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId, 'nome' => $name, 'cor' => $color, 'icone' => $icon]);
        return $statement->rowCount() > 0;
    }

    public function delete(string $id, string $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM disciplinas WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        return $statement->rowCount() > 0;
    }

    /** @return list<array<string, mixed>> */
    public function files(string $disciplineId, string $userId): array
    {
        $statement = $this->db->prepare('SELECT nome_original, nome_arquivo, extensao, criado_em FROM arquivos WHERE disciplina_id = :disciplina_id AND usuario_id = :usuario_id AND excluido = FALSE ORDER BY criado_em DESC LIMIT 8');
        $statement->execute(['disciplina_id' => $disciplineId, 'usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function tasks(string $disciplineId, string $userId): array
    {
        $statement = $this->db->prepare('SELECT titulo, prioridade, status, data_vencimento FROM tarefas WHERE disciplina_id = :disciplina_id AND usuario_id = :usuario_id ORDER BY data_vencimento IS NULL, data_vencimento ASC LIMIT 8');
        $statement->execute(['disciplina_id' => $disciplineId, 'usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function notes(string $disciplineId, string $userId): array
    {
        $statement = $this->db->prepare('SELECT titulo, atualizado_em FROM notas WHERE disciplina_id = :disciplina_id AND usuario_id = :usuario_id ORDER BY atualizado_em DESC LIMIT 8');
        $statement->execute(['disciplina_id' => $disciplineId, 'usuario_id' => $userId]);
        return $statement->fetchAll();
    }
}
