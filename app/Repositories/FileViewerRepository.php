<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FileViewerRepository extends Repository
{
    private const LOCATION_MARKER_TITLE = '__cfi__';

    /**
     * @return array<string,mixed>|null
     */
    public function findLibraryByFileId(string $fileId, string $userId): ?array
    {
        $sql = 'SELECT b.*, a.nome_original
                FROM biblioteca b
                INNER JOIN arquivos a ON a.id = b.arquivo_id
                WHERE b.arquivo_id = :fid AND a.usuario_id = :u AND a.excluido = FALSE
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fid' => $fileId, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findLibraryById(string $libraryId, string $userId): ?array
    {
        $sql = 'SELECT b.*, a.nome_original, a.id AS arquivo_id
                FROM biblioteca b
                INNER JOIN arquivos a ON a.id = b.arquivo_id
                WHERE b.id = :lid AND a.usuario_id = :u AND a.excluido = FALSE
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lid' => $libraryId, 'u' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @param array{id: string, arquivo_id: string, total_paginas?: int} $data
     */
    public function createLibraryEntry(array $data): void
    {
        $sql = 'INSERT INTO biblioteca (id, arquivo_id, pagina_atual, total_paginas, progresso_porcentagem)
                VALUES (:id, :arquivo_id, 1, :total, 0.00)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'          => $data['id'],
            'arquivo_id'  => $data['arquivo_id'],
            'total'       => max(0, (int) ($data['total_paginas'] ?? 0)),
        ]);
    }

    public function updateReadingProgress(
        string $libraryId,
        int $paginaAtual,
        float $progressoPct,
        ?string $cfi = null
    ): void {
        $sql = 'UPDATE biblioteca
                SET pagina_atual = :p, progresso_porcentagem = :prog, ultimo_acesso = CURRENT_TIMESTAMP
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'p'    => max(1, $paginaAtual),
            'prog' => min(100, max(0, $progressoPct)),
            'id'   => $libraryId,
        ]);

        if ($cfi !== null && $cfi !== '') {
            $this->upsertLocationCfi($libraryId, $cfi);
        }
    }

    public function updateTotalPages(string $libraryId, int $totalPages): void
    {
        $statement = $this->db->prepare('UPDATE biblioteca SET total_paginas = :total WHERE id = :id');
        $statement->execute(['id' => $libraryId, 'total' => max(0, $totalPages)]);
    }

    public function shelf(string $userId): array
    {
        $statement = $this->db->prepare(
            "SELECT b.*, a.id AS arquivo_id, a.nome_original, a.extensao, a.tipo_mime, a.criado_em AS arquivo_criado_em,
                    d.nome AS disciplina_nome, d.cor AS disciplina_cor
             FROM biblioteca b
             INNER JOIN arquivos a ON a.id = b.arquivo_id
             LEFT JOIN disciplinas d ON d.id = a.disciplina_id
             WHERE a.usuario_id = :user AND a.excluido = FALSE AND LOWER(a.extensao) IN ('pdf', 'epub')
             ORDER BY b.ultimo_acesso DESC, a.criado_em DESC"
        );
        $statement->execute(['user' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function availableForShelf(string $userId): array
    {
        $statement = $this->db->prepare(
            "SELECT a.id, a.nome_original, a.extensao, d.nome AS disciplina_nome
             FROM arquivos a LEFT JOIN disciplinas d ON d.id = a.disciplina_id
             LEFT JOIN biblioteca b ON b.arquivo_id = a.id
             WHERE a.usuario_id = :user AND a.excluido = FALSE AND b.id IS NULL AND LOWER(a.extensao) IN ('pdf', 'epub')
             ORDER BY a.criado_em DESC"
        );
        $statement->execute(['user' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recordReadingSession(string $libraryId, int $tempoSegundos, int $paginasLidas): void
    {
        $sql = 'INSERT INTO progresso_leitura (id, biblioteca_id, tempo_leitura_segundos, paginas_lidas_sessao)
                VALUES (:id, :bid, :t, :p)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'  => \App\Helpers\Uuid::v4(),
            'bid' => $libraryId,
            't'   => max(0, $tempoSegundos),
            'p'   => max(0, $paginasLidas),
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getProgressSummary(string $libraryId, string $userId): ?array
    {
        $library = $this->findLibraryById($libraryId, $userId);
        if ($library === null) {
            return null;
        }

        $sql = 'SELECT COALESCE(SUM(tempo_leitura_segundos), 0) AS tempo_total,
                       COALESCE(SUM(paginas_lidas_sessao), 0) AS paginas_total
                FROM progresso_leitura
                WHERE biblioteca_id = :bid';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $libraryId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'pagina_atual'           => (int) ($library['pagina_atual'] ?? 1),
            'total_paginas'          => (int) ($library['total_paginas'] ?? 1),
            'progresso_porcentagem'  => (float) ($library['progresso_porcentagem'] ?? 0),
            'ultimo_acesso'          => $library['ultimo_acesso'] ?? null,
            'cfi'                    => $this->getLocationCfi($libraryId),
            'tempo_leitura_segundos' => (int) ($stats['tempo_total'] ?? 0),
            'paginas_lidas_total'    => (int) ($stats['paginas_total'] ?? 0),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getBookmarks(string $libraryId, string $userId): array
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return [];
        }

        $sql = 'SELECT id, numero_pagina, titulo, criado_em
                FROM marcadores_livro
                WHERE biblioteca_id = :bid AND titulo NOT LIKE :skip
                ORDER BY numero_pagina ASC, criado_em ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $libraryId, 'skip' => self::LOCATION_MARKER_TITLE . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getHighlights(string $libraryId, string $userId): array
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return [];
        }

        $sql = 'SELECT id, numero_pagina, texto_selecionado, cor, criado_em
                FROM destaques_leitura
                WHERE biblioteca_id = :bid
                ORDER BY criado_em ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $libraryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getNotes(string $libraryId, string $userId): array
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return [];
        }

        $sql = 'SELECT n.id, n.destaque_id, n.conteudo, n.criado_em,
                       d.texto_selecionado, d.numero_pagina, d.cor
                FROM anotacoes_leitura n
                INNER JOIN destaques_leitura d ON d.id = n.destaque_id
                WHERE d.biblioteca_id = :bid
                ORDER BY n.criado_em ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $libraryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function addBookmark(string $libraryId, string $userId, int $numeroPagina, ?string $titulo): ?array
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return null;
        }

        $id = \App\Helpers\Uuid::v4();
        $sql = 'INSERT INTO marcadores_livro (id, biblioteca_id, numero_pagina, titulo)
                VALUES (:id, :bid, :p, :t)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'bid' => $libraryId,
            'p'   => $numeroPagina,
            't'   => $titulo !== null && $titulo !== '' ? mb_substr($titulo, 0, 150) : null,
        ]);

        return [
            'id'            => $id,
            'numero_pagina' => $numeroPagina,
            'titulo'        => $titulo,
        ];
    }

    public function deleteBookmark(string $libraryId, string $userId, string $bookmarkId): bool
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return false;
        }

        $sql = 'DELETE FROM marcadores_livro
                WHERE id = :id AND biblioteca_id = :bid AND titulo NOT LIKE :skip';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'   => $bookmarkId,
            'bid'  => $libraryId,
            'skip' => self::LOCATION_MARKER_TITLE . '%',
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function addHighlight(
        string $libraryId,
        string $userId,
        int $numeroPagina,
        string $texto,
        string $cor = '#FFFF00'
    ): ?array {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return null;
        }

        $id = \App\Helpers\Uuid::v4();
        $sql = 'INSERT INTO destaques_leitura (id, biblioteca_id, numero_pagina, texto_selecionado, cor)
                VALUES (:id, :bid, :p, :txt, :cor)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'bid' => $libraryId,
            'p'   => $numeroPagina,
            'txt' => mb_substr($texto, 0, 65000),
            'cor' => preg_match('/^#[0-9a-fA-F]{6}$/', $cor) ? $cor : '#FFFF00',
        ]);

        return [
            'id'                => $id,
            'numero_pagina'     => $numeroPagina,
            'texto_selecionado' => $texto,
            'cor'               => $cor,
        ];
    }

    public function deleteHighlight(string $libraryId, string $userId, string $highlightId): bool
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return false;
        }

        $sql = 'DELETE d FROM destaques_leitura d
                WHERE d.id = :id AND d.biblioteca_id = :bid';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $highlightId, 'bid' => $libraryId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function addNote(string $libraryId, string $userId, string $destaqueId, string $conteudo): ?array
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return null;
        }

        $check = $this->db->prepare(
            'SELECT id FROM destaques_leitura WHERE id = :did AND biblioteca_id = :bid LIMIT 1'
        );
        $check->execute(['did' => $destaqueId, 'bid' => $libraryId]);
        if ($check->fetch(PDO::FETCH_ASSOC) === false) {
            return null;
        }

        $id = \App\Helpers\Uuid::v4();
        $sql = 'INSERT INTO anotacoes_leitura (id, destaque_id, conteudo) VALUES (:id, :did, :c)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'did' => $destaqueId,
            'c'   => mb_substr($conteudo, 0, 65000),
        ]);

        return ['id' => $id, 'destaque_id' => $destaqueId, 'conteudo' => $conteudo];
    }

    public function deleteNote(string $libraryId, string $userId, string $noteId): bool
    {
        if ($this->findLibraryById($libraryId, $userId) === null) {
            return false;
        }

        $sql = 'DELETE n FROM anotacoes_leitura n
                INNER JOIN destaques_leitura d ON d.id = n.destaque_id
                WHERE n.id = :id AND d.biblioteca_id = :bid';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $noteId, 'bid' => $libraryId]);

        return $stmt->rowCount() > 0;
    }

    private function upsertLocationCfi(string $libraryId, string $cfi): void
    {
        $title = self::LOCATION_MARKER_TITLE . mb_substr($cfi, 0, 140);

        $find = $this->db->prepare(
            'SELECT id FROM marcadores_livro
             WHERE biblioteca_id = :bid AND titulo LIKE :prefix LIMIT 1'
        );
        $find->execute(['bid' => $libraryId, 'prefix' => self::LOCATION_MARKER_TITLE . '%']);
        $existing = $find->fetch(PDO::FETCH_ASSOC);

        if (is_array($existing)) {
            $upd = $this->db->prepare('UPDATE marcadores_livro SET titulo = :t WHERE id = :id');
            $upd->execute(['t' => $title, 'id' => $existing['id']]);

            return;
        }

        $ins = $this->db->prepare(
            'INSERT INTO marcadores_livro (id, biblioteca_id, numero_pagina, titulo)
             VALUES (:id, :bid, 0, :t)'
        );
        $ins->execute([
            'id'  => \App\Helpers\Uuid::v4(),
            'bid' => $libraryId,
            't'   => $title,
        ]);
    }

    private function getLocationCfi(string $libraryId): ?string
    {
        $sql = 'SELECT titulo FROM marcadores_livro
                WHERE biblioteca_id = :bid AND titulo LIKE :prefix
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $libraryId, 'prefix' => self::LOCATION_MARKER_TITLE . '%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row) || !isset($row['titulo'])) {
            return null;
        }

        $title = (string) $row['titulo'];
        if (!str_starts_with($title, self::LOCATION_MARKER_TITLE)) {
            return null;
        }

        $cfi = substr($title, strlen(self::LOCATION_MARKER_TITLE));

        return $cfi !== '' ? $cfi : null;
    }
}
