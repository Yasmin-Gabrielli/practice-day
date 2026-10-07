<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Helpers\Uuid;
use App\Repositories\FileRepository;
use App\Repositories\FileViewerRepository;
use App\Services\DocxHtmlConverter;

final class FileViewerService extends Service
{
    private FileRepository $fileRepo;
    private FileViewerRepository $viewerRepo;

    public function __construct(private readonly array $config)
    {
        $this->fileRepo   = new FileRepository($config['database']);
        $this->viewerRepo = new FileViewerRepository($config['database']);
    }

    /**
     * Retorna os metadados de um arquivo validando todos os requisitos de seguranca.
     * - Usuario autenticado (garantido pelo controller antes de chegar aqui)
     * - Arquivo pertence ao usuario
     * - Arquivo nao esta na lixeira
     * - Arquivo existe fisicamente no disco
     *
     * @return array<string,mixed>
     * @throws NotFoundException
     */
    public function getViewableFile(string $userId, string $fileId): array
    {
        $file = $this->fileRepo->file($fileId, $userId, false);

        if ($file === null) {
            throw new NotFoundException('Arquivo nao encontrado ou acesso nao autorizado.');
        }

        // Verificar existencia fisica
        $diskPath = $this->resolvePhysicalPath($file);
        if ($diskPath === null || !file_exists($diskPath)) {
            throw new NotFoundException('O arquivo nao esta disponivel no servidor.');
        }

        return $file;
    }

    /**
     * Prepara o arquivo para entrega HTTP segura.
     *
     * @return array{file: array<string,mixed>, path: string}
     * @throws NotFoundException
     */
    public function serveFile(string $userId, string $fileId): array
    {
        $file = $this->getViewableFile($userId, $fileId);
        $path = $this->resolvePhysicalPath($file);

        if ($path === null) {
            throw new NotFoundException('Caminho fisico do arquivo invalido.');
        }

        return ['file' => $file, 'path' => $path];
    }

    /**
     * Le o conteudo de um arquivo TXT de forma segura.
     * Limita a 2 MB para nao explodir a memoria.
     *
     * @throws NotFoundException
     */
    public function readTxtContent(string $userId, string $fileId): string
    {
        $file = $this->getViewableFile($userId, $fileId);
        $path = $this->resolvePhysicalPath($file);

        if ($path === null || !file_exists($path)) {
            throw new NotFoundException('Arquivo TXT nao disponivel.');
        }

        $maxBytes = 2 * 1024 * 1024; // 2 MB
        $content  = @file_get_contents($path, false, null, 0, $maxBytes);

        if ($content === false) {
            throw new NotFoundException('Nao foi possivel ler o conteudo do arquivo.');
        }

        // Detectar e converter encoding para UTF-8 se necessario
        if (!mb_check_encoding($content, 'UTF-8')) {
            $detected = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'UTF-16'], true);
            if ($detected !== false) {
                $converted = mb_convert_encoding($content, 'UTF-8', $detected);
                $content   = $converted !== false ? $converted : $content;
            }
        }

        return $content;
    }

    /**
     * Verifica se um arquivo de leitura ja esta catalogado na Biblioteca do usuario.
     * Se nao estiver, cria um vinculo (sem duplicar o arquivo fisico).
     *
     * @param array<string,mixed> $file
     * @return array<string,mixed>
     */
    public function getOrCreateLibraryEntry(string $userId, string $fileId, array $file): array
    {
        // Busca registro existente linkado ao arquivo
        $library = $this->viewerRepo->findLibraryByFileId($fileId, $userId);

        if ($library === null) {
            $libraryId = Uuid::v4();
            $this->viewerRepo->createLibraryEntry([
                'id'             => $libraryId,
                'arquivo_id'     => $fileId,
                'total_paginas'  => 0,
            ]);
            $library = $this->viewerRepo->findLibraryByFileId($fileId, $userId);
        }

        if ($library === null) {
            return [
                'id'         => null,
                'titulo'     => pathinfo((string) $file['nome_original'], PATHINFO_FILENAME),
                'progresso'  => null,
                'marcadores' => [],
                'destaques'  => [],
                'anotacoes'  => [],
            ];
        }

        $libId = (string) $library['id'];
        $titulo = pathinfo((string) ($library['nome_original'] ?? $file['nome_original']), PATHINFO_FILENAME);

        return [
            'id'         => $libId,
            'titulo'     => $titulo,
            'progresso'  => $this->viewerRepo->getProgressSummary($libId, $userId),
            'marcadores' => $this->viewerRepo->getBookmarks($libId, $userId),
            'destaques'  => $this->viewerRepo->getHighlights($libId, $userId),
            'anotacoes'  => $this->viewerRepo->getNotes($libId, $userId),
        ];
    }

    public function shelf(string $userId): array
    {
        return ['books' => $this->viewerRepo->shelf($userId), 'available' => $this->viewerRepo->availableForShelf($userId)];
    }

    public function addToShelf(string $userId, string $fileId): void
    {
        $file = $this->getViewableFile($userId, $fileId);
        if (!in_array(strtolower((string) ($file['extensao'] ?? '')), ['pdf', 'epub'], true)) {
            throw new \App\Exceptions\ValidationException(['arquivo' => 'A biblioteca aceita apenas arquivos PDF ou EPUB.']);
        }
        $this->getOrCreateLibraryEntry($userId, $fileId, $file);
    }

    /**
     * @return array{html: string, engine: string, message: string|null}
     * @throws NotFoundException
     */
    public function convertDocxToHtml(string $userId, string $fileId): array
    {
        $file = $this->getViewableFile($userId, $fileId);
        $ext  = strtolower((string) ($file['extensao'] ?? ''));
        if ($ext !== 'docx') {
            throw new NotFoundException('Arquivo não é DOCX.');
        }

        $path = $this->resolvePhysicalPath($file);
        if ($path === null) {
            throw new NotFoundException('Caminho do arquivo inválido.');
        }

        return (new DocxHtmlConverter())->convertFile($path);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function saveReadingProgress(string $userId, string $libraryId, array $payload): array
    {
        $library = $this->viewerRepo->findLibraryById($libraryId, $userId);
        if ($library === null) {
            throw new NotFoundException('Registro de biblioteca não encontrado.');
        }

        $pagina   = (int) ($payload['pagina_atual'] ?? 1);
        $progress = (float) ($payload['progresso_porcentagem'] ?? 0);
        $cfi      = isset($payload['cfi']) ? (string) $payload['cfi'] : null;
        $total    = isset($payload['total_paginas']) ? (int) $payload['total_paginas'] : 0;

        if ($total > 0) {
            $this->viewerRepo->updateTotalPages($libraryId, $total);
        }

        $this->viewerRepo->updateReadingProgress($libraryId, $pagina, $progress, $cfi);

        $tempo   = (int) ($payload['tempo_leitura_segundos'] ?? 0);
        $paginas = (int) ($payload['paginas_lidas_sessao'] ?? 0);
        if ($tempo > 0 || $paginas > 0) {
            $this->viewerRepo->recordReadingSession($libraryId, $tempo, $paginas);
        }

        return $this->viewerRepo->getProgressSummary($libraryId, $userId) ?? [];
    }

    /**
     * @return array<string,mixed>
     */
    public function assertLibraryAccess(string $userId, string $libraryId): array
    {
        $library = $this->viewerRepo->findLibraryById($libraryId, $userId);
        if ($library === null) {
            throw new NotFoundException('Acesso negado à biblioteca.');
        }

        return $library;
    }

    // -----------------------------------------------------------------------
    // Privado
    // -----------------------------------------------------------------------

    /**
     * Resolve o caminho fisico absoluto do arquivo e valida path traversal.
     */
    private function resolvePhysicalPath(array $file): ?string
    {
        $storagePath = (string) ($file['caminho_armazenamento'] ?? '');
        $base        = realpath(dirname(__DIR__, 2) . '/storage');

        if ($base === false || $storagePath === '') {
            return null;
        }

        // Construir o caminho completo e resolver simbolicos
        $full = realpath($base . '/' . $storagePath);

        if ($full === false) {
            return null;
        }

        // Garantir que o arquivo esta dentro da pasta storage (evitar path traversal)
        $normalBase = rtrim(str_replace('\\', '/', $base), '/') . '/';
        $normalFull = str_replace('\\', '/', $full);

        if (!str_starts_with($normalFull, $normalBase)) {
            return null;
        }

        return $full;
    }
}
