<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Uuid;
use PDO;

final class FileRepository extends Repository
{
    /**
     * @return list<array<string,mixed>>
     */
    public function files(
        string $userId,
        ?string $folderId = null,
        bool $onlyFavorites = false,
        ?string $disciplineId = null,
        ?string $search = null
    ): array {
        $sql = 'SELECT 
                    a.*,
                    d.nome AS disciplina_nome,
                    d.cor AS disciplina_cor,
                    d.icone AS disciplina_icone,
                    p.nome AS pasta_nome,
                    EXISTS(SELECT 1 FROM arquivos_favoritos af WHERE af.arquivo_id = a.id AND af.usuario_id = a.usuario_id) AS favorito_real,
                    GROUP_CONCAT(CONCAT(t.id, ":::", t.nome, ":::", COALESCE(t.cor, "#2563eb")) SEPARATOR "|||") AS tags_raw
                FROM arquivos a
                LEFT JOIN disciplinas d ON d.id = a.disciplina_id
                LEFT JOIN pastas p ON p.id = a.pasta_id
                LEFT JOIN tags_arquivo ta ON ta.arquivo_id = a.id
                LEFT JOIN tags t ON t.id = ta.tag_id
                WHERE a.usuario_id = :u AND a.excluido = FALSE';

        $params = ['u' => $userId];

        if ($folderId === 'root') {
            $sql .= ' AND a.pasta_id IS NULL';
        } elseif ($folderId !== null && $folderId !== '') {
            $sql .= ' AND a.pasta_id = :p';
            $params['p'] = $folderId;
        }

        if ($onlyFavorites) {
            $sql .= ' AND (a.favorito = TRUE OR EXISTS(SELECT 1 FROM arquivos_favoritos af WHERE af.arquivo_id = a.id AND af.usuario_id = a.usuario_id))';
        }

        if ($disciplineId !== null && $disciplineId !== '') {
            $sql .= ' AND a.disciplina_id = :d';
            $params['d'] = $disciplineId;
        }

        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND (a.nome_original LIKE :s OR a.extensao LIKE :s OR d.nome LIKE :s)';
            $params['s'] = '%' . trim($search) . '%';
        }

        $sql .= ' GROUP BY a.id ORDER BY a.criado_em DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatFileRow'], $rows);
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function trashFiles(string $userId, ?string $search = null): array
    {
        $sql = 'SELECT 
                    a.*,
                    d.nome AS disciplina_nome,
                    d.cor AS disciplina_cor,
                    p.nome AS pasta_nome,
                    l.excluido_em,
                    l.expira_em,
                    GROUP_CONCAT(CONCAT(t.id, ":::", t.nome, ":::", COALESCE(t.cor, "#2563eb")) SEPARATOR "|||") AS tags_raw
                FROM arquivos a
                JOIN lixeira_arquivos l ON l.arquivo_id = a.id AND l.usuario_id = a.usuario_id
                LEFT JOIN disciplinas d ON d.id = a.disciplina_id
                LEFT JOIN pastas p ON p.id = a.pasta_id
                LEFT JOIN tags_arquivo ta ON ta.arquivo_id = a.id
                LEFT JOIN tags t ON t.id = ta.tag_id
                WHERE a.usuario_id = :u AND a.excluido = TRUE';

        $params = ['u' => $userId];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND (a.nome_original LIKE :s OR a.extensao LIKE :s)';
            $params['s'] = '%' . trim($search) . '%';
        }

        $sql .= ' GROUP BY a.id ORDER BY l.excluido_em DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatFileRow'], $rows);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function file(string $id, string $userId, bool $includeTrashed = false): ?array
    {
        $sql = 'SELECT 
                    a.*,
                    d.nome AS disciplina_nome,
                    d.cor AS disciplina_cor,
                    p.nome AS pasta_nome,
                    EXISTS(SELECT 1 FROM arquivos_favoritos af WHERE af.arquivo_id = a.id AND af.usuario_id = a.usuario_id) AS favorito_real,
                    GROUP_CONCAT(CONCAT(t.id, ":::", t.nome, ":::", COALESCE(t.cor, "#2563eb")) SEPARATOR "|||") AS tags_raw
                FROM arquivos a
                LEFT JOIN disciplinas d ON d.id = a.disciplina_id
                LEFT JOIN pastas p ON p.id = a.pasta_id
                LEFT JOIN tags_arquivo ta ON ta.arquivo_id = a.id
                LEFT JOIN tags t ON t.id = ta.tag_id
                WHERE a.id = :id AND a.usuario_id = :u';

        if (!$includeTrashed) {
            $sql .= ' AND a.excluido = FALSE';
        }

        $sql .= ' GROUP BY a.id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->formatFileRow($row) : null;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function folders(string $userId, ?string $parentFolderId = null, ?string $disciplineId = null): array
    {
        $sql = 'SELECT 
                    p.*,
                    d.nome AS disciplina_nome,
                    d.cor AS disciplina_cor,
                    (SELECT COUNT(*) FROM arquivos a WHERE a.pasta_id = p.id AND a.excluido = FALSE AND a.usuario_id = :u1) AS total_arquivos,
                    (SELECT COUNT(*) FROM pastas sub WHERE sub.pasta_pai_id = p.id) AS total_subpastas
                FROM pastas p
                LEFT JOIN disciplinas d ON d.id = p.disciplina_id
                LEFT JOIN permissoes_pasta pp ON pp.pasta_id = p.id
                WHERE (d.usuario_id = :u2 OR pp.usuario_id = :u3)';

        $params = [
            'u1' => $userId,
            'u2' => $userId,
            'u3' => $userId,
        ];

        if ($parentFolderId === 'root') {
            $sql .= ' AND p.pasta_pai_id IS NULL';
        } elseif ($parentFolderId !== null && $parentFolderId !== '') {
            $sql .= ' AND p.pasta_pai_id = :parent';
            $params['parent'] = $parentFolderId;
        }

        if ($disciplineId !== null && $disciplineId !== '') {
            $sql .= ' AND p.disciplina_id = :disc';
            $params['disc'] = $disciplineId;
        }

        $sql .= ' GROUP BY p.id ORDER BY p.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todas as pastas do usuário para selects/combos.
     * @return list<array<string,mixed>>
     */
    public function allFolders(string $userId): array
    {
        $sql = 'SELECT 
                    p.id,
                    p.nome,
                    p.pasta_pai_id,
                    p.disciplina_id,
                    d.nome AS disciplina_nome
                FROM pastas p
                LEFT JOIN disciplinas d ON d.id = p.disciplina_id
                LEFT JOIN permissoes_pasta pp ON pp.pasta_id = p.id
                WHERE (d.usuario_id = :u1 OR pp.usuario_id = :u2)
                ORDER BY p.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u1' => $userId, 'u2' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function ownedFolder(string $id, string $userId): ?array
    {
        $sql = 'SELECT 
                    p.*,
                    d.nome AS disciplina_nome,
                    d.cor AS disciplina_cor
                FROM pastas p
                LEFT JOIN disciplinas d ON d.id = p.disciplina_id
                LEFT JOIN permissoes_pasta pp ON pp.pasta_id = p.id
                WHERE p.id = :id AND (d.usuario_id = :u1 OR pp.usuario_id = :u2)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'u1' => $userId, 'u2' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * Retorna a lista de pastas ancestrais de uma pasta (para breadcrumb).
     * @return list<array{id: string, nome: string}>
     */
    public function folderAncestors(string $folderId, string $userId): array
    {
        $ancestors = [];
        $currentId = $folderId;
        $visited = [];

        while ($currentId !== null && $currentId !== '' && !isset($visited[$currentId])) {
            $visited[$currentId] = true;
            $folder = $this->ownedFolder($currentId, $userId);
            if ($folder === null) {
                break;
            }
            array_unshift($ancestors, [
                'id' => (string) $folder['id'],
                'nome' => (string) $folder['nome'],
                'pasta_pai_id' => $folder['pasta_pai_id'] !== null ? (string) $folder['pasta_pai_id'] : null,
                'disciplina_nome' => (string) ($folder['disciplina_nome'] ?? ''),
            ]);
            $currentId = $folder['pasta_pai_id'] !== null ? (string) $folder['pasta_pai_id'] : null;
        }

        return $ancestors;
    }

    public function createFolder(
        string $id,
        ?string $disciplineId,
        ?string $parentId,
        string $name,
        string $userId
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO pastas (id, disciplina_id, pasta_pai_id, nome) VALUES (:id, :d, :p, :n)'
        );
        $stmt->execute([
            'id' => $id,
            'd' => $disciplineId ?: null,
            'p' => $parentId ?: null,
            'n' => $name,
        ]);

        // Registrar também permissão na tabela permissoes_pasta
        $permStmt = $this->db->prepare(
            'INSERT INTO permissoes_pasta (id, pasta_id, usuario_id, tipo_permissao) VALUES (:id, :p, :u, :t)'
        );
        $permStmt->execute([
            'id' => Uuid::v4(),
            'p' => $id,
            'u' => $userId,
            't' => 'administrador',
        ]);
    }

    public function renameFolder(string $id, string $name): void
    {
        $stmt = $this->db->prepare('UPDATE pastas SET nome = :n WHERE id = :id');
        $stmt->execute(['id' => $id, 'n' => $name]);
    }

    public function moveFolder(string $id, ?string $parentId): void
    {
        $stmt = $this->db->prepare('UPDATE pastas SET pasta_pai_id = :p WHERE id = :id');
        $stmt->execute(['id' => $id, 'p' => $parentId ?: null]);
    }

    public function deleteFolder(string $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM pastas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param array{id: string, p: ?string, d: ?string, u: string, saved: string, original: string, ext: string, mime: string, size: int, path: string} $d
     */
    public function createFile(array $d): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO arquivos (id, pasta_id, disciplina_id, usuario_id, nome_arquivo, nome_original, extensao, tipo_mime, tamanho_bytes, caminho_armazenamento) 
             VALUES (:id, :p, :d, :u, :saved, :original, :ext, :mime, :size, :path)'
        );
        $stmt->execute($d);
    }

    /**
     * @param list<string> $tags
     */
    public function attachTags(string $fileId, array $tags): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO tags_arquivo (arquivo_id, tag_id) VALUES (:f, :t)');
        foreach ($tags as $tagId) {
            $stmt->execute(['f' => $fileId, 't' => $tagId]);
        }
    }

    /**
     * @param list<string> $tags
     */
    public function syncTags(string $fileId, array $tags): void
    {
        $del = $this->db->prepare('DELETE FROM tags_arquivo WHERE arquivo_id = :f');
        $del->execute(['f' => $fileId]);

        if ($tags !== []) {
            $this->attachTags($fileId, $tags);
        }
    }

    /**
     * @return list<array{id: string, nome: string, cor: string}>
     */
    public function fileTags(string $fileId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.id, t.nome, t.cor 
             FROM tags t 
             JOIN tags_arquivo ta ON ta.tag_id = t.id 
             WHERE ta.arquivo_id = :f 
             ORDER BY t.nome'
        );
        $stmt->execute(['f' => $fileId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array{id: string, nome: string, cor: ?string}>
     */
    public function tags(string $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, nome, cor FROM tags WHERE usuario_id = :u ORDER BY nome');
        $stmt->execute(['u' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTag(string $id, string $userId, string $name, string $color): void
    {
        $stmt = $this->db->prepare('INSERT INTO tags (id, usuario_id, nome, cor) VALUES (:id, :u, :n, :c)');
        $stmt->execute([
            'id' => $id,
            'u' => $userId,
            'n' => $name,
            'c' => $color,
        ]);
    }

    public function ownedDiscipline(string $id, string $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM disciplinas WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function disciplines(string $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, nome, cor, icone FROM disciplinas WHERE usuario_id = :u ORDER BY nome');
        $stmt->execute(['u' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rename(string $id, string $userId, string $newName): void
    {
        $stmt = $this->db->prepare('UPDATE arquivos SET nome_original = :n WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId, 'n' => $newName]);
    }

    public function moveFile(string $id, string $userId, ?string $folderId, ?string $disciplineId): void
    {
        $stmt = $this->db->prepare('UPDATE arquivos SET pasta_id = :p, disciplina_id = :d WHERE id = :id AND usuario_id = :u');
        $stmt->execute([
            'id' => $id,
            'u' => $userId,
            'p' => $folderId ?: null,
            'd' => $disciplineId ?: null,
        ]);
    }

    public function favorite(string $id, string $userId, bool $on): void
    {
        if ($on) {
            $stmt = $this->db->prepare('INSERT IGNORE INTO arquivos_favoritos (usuario_id, arquivo_id) VALUES (:u, :f)');
            $stmt->execute(['u' => $userId, 'f' => $id]);
        } else {
            $stmt = $this->db->prepare('DELETE FROM arquivos_favoritos WHERE usuario_id = :u AND arquivo_id = :f');
            $stmt->execute(['u' => $userId, 'f' => $id]);
        }

        $stmt = $this->db->prepare('UPDATE arquivos SET favorito = :v WHERE id = :f AND usuario_id = :u');
        $stmt->execute(['v' => $on ? 1 : 0, 'f' => $id, 'u' => $userId]);
    }

    public function moveToTrash(string $id, string $userId): void
    {
        $stmt = $this->db->prepare('UPDATE arquivos SET excluido = TRUE WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);

        $trashStmt = $this->db->prepare(
            'INSERT INTO lixeira_arquivos (id, arquivo_id, usuario_id, excluido_em, expira_em) 
             VALUES (:lid, :id, :u, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))'
        );
        $trashStmt->execute([
            'lid' => Uuid::v4(),
            'id' => $id,
            'u' => $userId,
        ]);
    }

    public function restoreFromTrash(string $id, string $userId): void
    {
        $stmt = $this->db->prepare('UPDATE arquivos SET excluido = FALSE WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);

        $delStmt = $this->db->prepare('DELETE FROM lixeira_arquivos WHERE arquivo_id = :id AND usuario_id = :u');
        $delStmt->execute(['id' => $id, 'u' => $userId]);
    }

    public function deletePermanently(string $id, string $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM arquivos WHERE id = :id AND usuario_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
    }

    public function history(string $fileId, string $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.nome AS usuario_nome 
             FROM historico_arquivo h 
             JOIN usuarios u ON u.id = h.usuario_id 
             WHERE h.arquivo_id = :f AND h.usuario_id = :u 
             ORDER BY h.criado_em DESC'
        );
        $stmt->execute(['f' => $fileId, 'u' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addHistory(string $id, string $fileId, string $userId, string $action, string $details): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO historico_arquivo (id, arquivo_id, usuario_id, acao, detalhes) 
             VALUES (:id, :f, :u, :a, :d)'
        );
        $stmt->execute([
            'id' => $id,
            'f' => $fileId,
            'u' => $userId,
            'a' => $action,
            'd' => $details,
        ]);
    }

    /**
     * @return array{total_arquivos: int, total_bytes: int, total_favoritos: int, total_lixeira: int}
     */
    public function stats(string $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(CASE WHEN a.excluido = FALSE THEN 1 END) AS total_arquivos,
                COALESCE(SUM(CASE WHEN a.excluido = FALSE THEN a.tamanho_bytes ELSE 0 END), 0) AS total_bytes,
                COUNT(CASE WHEN a.excluido = FALSE AND (a.favorito = TRUE OR af.arquivo_id IS NOT NULL) THEN 1 END) AS total_favoritos,
                COUNT(CASE WHEN a.excluido = TRUE THEN 1 END) AS total_lixeira
             FROM arquivos a
             LEFT JOIN arquivos_favoritos af ON af.arquivo_id = a.id AND af.usuario_id = a.usuario_id
             WHERE a.usuario_id = :u'
        );
        $stmt->execute(['u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_arquivos' => (int) ($row['total_arquivos'] ?? 0),
            'total_bytes' => (int) ($row['total_bytes'] ?? 0),
            'total_favoritos' => (int) ($row['total_favoritos'] ?? 0),
            'total_lixeira' => (int) ($row['total_lixeira'] ?? 0),
        ];
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function formatFileRow(array $row): array
    {
        $tags = [];
        if (!empty($row['tags_raw'])) {
            $items = explode('|||', (string) $row['tags_raw']);
            foreach ($items as $item) {
                $parts = explode(':::', $item);
                if (count($parts) >= 3) {
                    $tags[] = [
                        'id' => $parts[0],
                        'nome' => $parts[1],
                        'cor' => $parts[2],
                    ];
                }
            }
        }
        $row['tags'] = $tags;

        return $row;
    }
}
