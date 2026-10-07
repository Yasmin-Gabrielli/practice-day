<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ScannerRepository extends Repository
{
    public function begin(): void { $this->db->beginTransaction(); }
    public function commit(): void { $this->db->commit(); }
    public function rollbackIfNeeded(): void { if ($this->db->inTransaction()) $this->db->rollBack(); }
    public function scans(string $userId): array
    {
        $statement = $this->db->prepare('SELECT s.*, d.nome AS disciplina_nome, d.cor AS disciplina_cor, MAX(p.criado_em) AS ultima_pagina FROM digitalizacoes s LEFT JOIN disciplinas d ON d.id = s.disciplina_id LEFT JOIN paginas_digitalizacao p ON p.digitalizacao_id = s.id LEFT JOIN arquivos a ON a.usuario_id = s.usuario_id AND a.excluido = FALSE AND a.caminho_armazenamento LIKE CONCAT(\'scanner/\', s.id, \'/%\') WHERE s.usuario_id = :user GROUP BY s.id HAVING COUNT(a.id) > 0 ORDER BY s.criado_em DESC');
        $statement->execute(['user' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string, string> */
    public function ocrTexts(string $userId): array
    {
        $statement = $this->db->prepare('SELECT p.digitalizacao_id AS scan_id, co.texto_extraido FROM conteudos_ocr co INNER JOIN paginas_digitalizacao p ON p.id = co.pagina_digitalizacao_id INNER JOIN digitalizacoes s ON s.id = p.digitalizacao_id WHERE s.usuario_id = :user AND co.texto_extraido IS NOT NULL AND co.texto_extraido <> \'\' ORDER BY p.numero_pagina');
        $statement->execute(['user' => $userId]);
        $texts = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $scanId = (string) $row['scan_id'];
            $texts[$scanId] = trim(($texts[$scanId] ?? '') . "\n" . trim((string) $row['texto_extraido']));
        }
        return $texts;
    }

    public function saveOcrText(string $pageId, string $text): void
    {
        $statement = $this->db->prepare('UPDATE conteudos_ocr SET texto_extraido = :text WHERE pagina_digitalizacao_id = :page');
        $statement->execute(['text' => $text, 'page' => $pageId]);
    }

    public function findForUser(string $id, string $userId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM digitalizacoes WHERE id = :id AND usuario_id = :user');
        $statement->execute(['id' => $id, 'user' => $userId]);
        $scan = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($scan) ? $scan : null;
    }

    /** @return list<string> */
    public function scanFilePaths(string $scanId, string $userId): array
    {
        $statement = $this->db->prepare('SELECT caminho_armazenamento FROM arquivos WHERE usuario_id = :user AND caminho_armazenamento LIKE :prefix');
        $statement->execute(['user' => $userId, 'prefix' => 'scanner/' . $scanId . '/%']);
        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function deleteScan(string $scanId, string $userId): void
    {
        $this->db->prepare('DELETE FROM arquivos WHERE usuario_id = :user AND caminho_armazenamento LIKE :prefix')
            ->execute(['user' => $userId, 'prefix' => 'scanner/' . $scanId . '/%']);
        $this->db->prepare('DELETE FROM digitalizacoes WHERE id = :id AND usuario_id = :user')
            ->execute(['id' => $scanId, 'user' => $userId]);
    }

    public function createScan(string $id, string $userId, string $title, ?string $disciplineId): void
    {
        $statement = $this->db->prepare('INSERT INTO digitalizacoes (id, usuario_id, disciplina_id, titulo, total_paginas) VALUES (:id, :user, :discipline, :title, 0)');
        $statement->execute(['id' => $id, 'user' => $userId, 'discipline' => $disciplineId, 'title' => $title]);
    }

    public function addPage(string $id, string $scanId, int $number, string $path): void
    {
        $statement = $this->db->prepare('INSERT INTO paginas_digitalizacao (id, digitalizacao_id, numero_pagina, caminho_imagem) VALUES (:id, :scan, :number, :path)');
        $statement->execute(['id' => $id, 'scan' => $scanId, 'number' => $number, 'path' => $path]);
    }

    public function prepareOcr(string $id, string $pageId, string $fileId, string $language): void
    {
        $statement = $this->db->prepare('INSERT INTO conteudos_ocr (id, arquivo_id, pagina_digitalizacao_id, texto_extraido, idioma, precisao) VALUES (:id, :file, :page, NULL, :language, NULL)');
        $statement->execute(['id' => $id, 'file' => $fileId, 'page' => $pageId, 'language' => $language]);
    }

    public function finishScan(string $scanId, int $pages): void
    {
        $statement = $this->db->prepare('UPDATE digitalizacoes SET total_paginas = :pages WHERE id = :id');
        $statement->execute(['id' => $scanId, 'pages' => $pages]);
    }

    public function pageForUser(string $pageId, string $userId): ?array
    {
        $statement = $this->db->prepare('SELECT p.* FROM paginas_digitalizacao p INNER JOIN digitalizacoes s ON s.id = p.digitalizacao_id WHERE p.id = :page AND s.usuario_id = :user');
        $statement->execute(['page' => $pageId, 'user' => $userId]);
        $page = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($page) ? $page : null;
    }
}
