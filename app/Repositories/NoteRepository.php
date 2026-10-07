<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Uuid;
use PDO;

final class NoteRepository extends Repository
{
    public function list(string $userId, ?string $search, ?string $disciplineId): array
    {
        $sql = 'SELECT n.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor,
                    (SELECT COUNT(*) FROM blocos_nota b WHERE b.nota_id = n.id) AS blocos_count
                FROM notas n
                LEFT JOIN disciplinas d ON d.id = n.disciplina_id
                WHERE n.usuario_id = :user';
        $params = ['user' => $userId];
        if ($disciplineId !== null && $disciplineId !== '') {
            $sql .= ' AND n.disciplina_id = :discipline';
            $params['discipline'] = $disciplineId;
        }
        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND (n.titulo LIKE :search_title OR n.conteudo LIKE :search_content OR EXISTS (SELECT 1 FROM blocos_nota b WHERE b.nota_id = n.id AND b.conteudo LIKE :search_blocks))';
            $term = '%' . trim($search) . '%';
            $params['search_title'] = $term;
            $params['search_content'] = $term;
            $params['search_blocks'] = $term;
        }
        $sql .= ' ORDER BY n.atualizado_em DESC, n.criado_em DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recent(string $userId, int $limit = 5): array
    {
        $statement = $this->db->prepare(
            'SELECT n.id, n.titulo, n.atualizado_em, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM notas n LEFT JOIN disciplinas d ON d.id = n.disciplina_id
             WHERE n.usuario_id = :user ORDER BY n.atualizado_em DESC LIMIT ' . (int) $limit
        );
        $statement->execute(['user' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $userId, string $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT n.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM notas n LEFT JOIN disciplinas d ON d.id = n.disciplina_id
             WHERE n.id = :id AND n.usuario_id = :user'
        );
        $statement->execute(['id' => $id, 'user' => $userId]);
        $note = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($note)) return null;
        $blocks = $this->db->prepare('SELECT * FROM blocos_nota WHERE nota_id = :note ORDER BY ordem ASC, criado_em ASC');
        $blocks->execute(['note' => $id]);
        $note['blocos'] = $blocks->fetchAll(PDO::FETCH_ASSOC);
        return $note;
    }

    public function create(string $id, string $userId, ?string $disciplineId, string $title, ?string $content, array $blocks): void
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('INSERT INTO notas (id, usuario_id, disciplina_id, titulo, conteudo) VALUES (:id, :user, :discipline, :title, :content)');
            $statement->execute(['id' => $id, 'user' => $userId, 'discipline' => $disciplineId, 'title' => $title, 'content' => $content]);
            $this->replaceBlocks($id, $blocks);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function update(string $id, string $userId, ?string $disciplineId, string $title, ?string $content, array $blocks): void
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('UPDATE notas SET disciplina_id = :discipline, titulo = :title, conteudo = :content WHERE id = :id AND usuario_id = :user');
            $statement->execute(['id' => $id, 'user' => $userId, 'discipline' => $disciplineId, 'title' => $title, 'content' => $content]);
            $delete = $this->db->prepare('DELETE FROM blocos_nota WHERE nota_id = :note');
            $delete->execute(['note' => $id]);
            $this->replaceBlocks($id, $blocks);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(string $userId, string $id): void
    {
        $statement = $this->db->prepare('DELETE FROM notas WHERE id = :id AND usuario_id = :user');
        $statement->execute(['id' => $id, 'user' => $userId]);
    }

    private function replaceBlocks(string $noteId, array $blocks): void
    {
        if ($blocks === []) return;
        $statement = $this->db->prepare('INSERT INTO blocos_nota (id, nota_id, tipo_bloco, conteudo, ordem) VALUES (:id, :note, :type, :content, :position)');
        foreach ($blocks as $position => $block) {
            $statement->execute(['id' => Uuid::v4(), 'note' => $noteId, 'type' => $block['tipo'], 'content' => $block['conteudo'], 'position' => $position + 1]);
        }
    }
}
