<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Helpers\Uuid;
use App\Repositories\FileRepository;
use App\Repositories\FileViewerRepository;

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
     * Verifica se o EPUB ja esta catalogado na Biblioteca do usuario.
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
            // Cria vinculo na tabela biblioteca
            $libraryId = Uuid::v4();
            $titulo    = pathinfo((string) $file['nome_original'], PATHINFO_FILENAME);
            $this->viewerRepo->createLibraryEntry([
                'id'          => $libraryId,
                'usuario_id'  => $userId,
                'titulo'      => mb_substr($titulo, 0, 200),
                'arquivo_id'  => $fileId,
                'caminho'     => (string) $file['caminho_armazenamento'],
                'formato'     => 'epub',
            ]);
            $library = $this->viewerRepo->findLibraryByFileId($fileId, $userId);
        }

        if ($library === null) {
            return ['id' => null, 'progresso' => null, 'marcadores' => [], 'destaques' => [], 'anotacoes' => []];
        }

        $libId = (string) $library['id'];

        return [
            'id'         => $libId,
            'titulo'     => $library['titulo'] ?? $file['nome_original'],
            'progresso'  => $this->viewerRepo->getProgress($libId, $userId),
            'marcadores' => $this->viewerRepo->getBookmarks($libId, $userId),
            'destaques'  => $this->viewerRepo->getHighlights($libId, $userId),
            'anotacoes'  => $this->viewerRepo->getNotes($libId, $userId),
        ];
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
