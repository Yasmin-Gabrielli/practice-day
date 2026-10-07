<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Uuid;
use App\Repositories\DisciplineRepository;
use App\Repositories\FileRepository;
use App\Repositories\ScannerRepository;

final class ScannerService extends Service
{
    private ScannerRepository $scans;
    private DisciplineRepository $disciplines;
    private FileRepository $files;

    public function __construct(private readonly array $config) { $this->scans = new ScannerRepository($config['database']); $this->disciplines = new DisciplineRepository($config['database']); $this->files = new FileRepository($config['database']); }

    public function list(string $userId): array
    {
        $scans = $this->scans->scans($userId);
        $texts = $this->scans->ocrTexts($userId);
        foreach ($scans as &$scan) {
            $scan['texto_ocr'] = $texts[(string) $scan['id']] ?? '';
        }
        unset($scan);
        return $scans;
    }

    public function disciplines(string $userId): array { return $this->disciplines->allForUser($userId); }

    public function create(string $userId, array $data, array $uploads): void
    {
        $title = trim((string) ($data['titulo'] ?? ''));
        $discipline = trim((string) ($data['disciplina_id'] ?? ''));
        $language = in_array(($data['idioma_ocr'] ?? 'por'), ['por', 'eng', 'spa'], true) ? (string) $data['idioma_ocr'] : 'por';
        if ($title === '') throw new ValidationException(['titulo' => 'Informe um título para a digitalização.']);
        if ($discipline !== '' && $this->disciplines->findForUser($discipline, $userId) === null) throw new ValidationException(['disciplina_id' => 'Disciplina inválida.']);
        $files = $this->normaliseUploads($uploads);
        if ($files === []) throw new ValidationException(['paginas' => 'Capture ou selecione ao menos uma imagem.']);

        $scanId = Uuid::v4(); $stored = [];
        $this->scans->begin();
        try {
            $this->scans->createScan($scanId, $userId, mb_substr($title, 0, 255), $discipline === '' ? null : $discipline);
            foreach ($files as $number => $file) {
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                $extension = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) throw new ValidationException(['paginas' => 'Cada página deve ser uma imagem JPEG, PNG ou WEBP.']);
                $dir = dirname(__DIR__, 2) . '/storage/scanner/' . $scanId;
                if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new \RuntimeException('Não foi possível preparar o armazenamento das páginas.');
                $pageId = Uuid::v4(); $relative = 'scanner/' . $scanId . '/' . $pageId . '.' . $extension; $target = dirname(__DIR__, 2) . '/storage/' . $relative;
                if (!move_uploaded_file($file['tmp_name'], $target)) throw new \RuntimeException('Não foi possível salvar uma página capturada.');
                $stored[] = ['path' => $target, 'page' => $pageId];
                $fileId = Uuid::v4();
                $this->files->createFile(['id' => $fileId, 'p' => null, 'd' => $discipline === '' ? null : $discipline, 'u' => $userId, 'saved' => $pageId . '.' . $extension, 'original' => 'Digitalização ' . $title . ' - página ' . ($number + 1) . '.' . $extension, 'ext' => $extension, 'mime' => $mime, 'size' => filesize($target), 'path' => $relative]);
                $this->scans->addPage($pageId, $scanId, $number + 1, $relative);
                $this->scans->prepareOcr(Uuid::v4(), $pageId, $fileId, $language);
            }
            $this->scans->finishScan($scanId, count($files)); $this->scans->commit();
        } catch (\Throwable $e) {
            $this->scans->rollbackIfNeeded();
            foreach ($stored as $page) if (is_file($page['path'])) unlink($page['path']);
            throw $e;
        }

        $this->extractText($stored, $language);
    }

    /** @param list<array{path: string, page: string}> $pages */
    private function extractText(array $pages, string $language): void
    {
        if (!TesseractOcr::available()) return;

        foreach ($pages as $page) {
            try {
                $text = TesseractOcr::recognize($page['path'], $language);
                if ($text !== null) {
                    $this->scans->saveOcrText($page['page'], $text);
                }
            } catch (\Throwable $e) {
                // OCR é opcional: falha no reconhecimento não invalida a digitalização.
            }
        }
    }

    public function delete(string $userId, string $scanId): void
    {
        $scan = $this->scans->findForUser($scanId, $userId);
        if ($scan === null) throw new NotFoundException('Digitalização não encontrada.');

        $paths = $this->scans->scanFilePaths($scanId, $userId);
        $this->scans->deleteScan($scanId, $userId);

        $base = realpath(dirname(__DIR__, 2) . '/storage/scanner/' . $scanId);
        foreach ($paths as $relative) {
            $full = realpath(dirname(__DIR__, 2) . '/storage/' . $relative);
            if ($full !== false && $base !== false && str_starts_with($full, $base) && is_file($full)) {
                @unlink($full);
            }
        }
        if ($base !== false && is_dir($base)) {
            @rmdir($base);
        }
    }

    public function ocrAvailable(): bool { return TesseractOcr::available(); }

    public function page(string $userId, string $id): array
    {
        $page = $this->scans->pageForUser($id, $userId);
        if ($page === null) throw new NotFoundException('Página digitalizada não encontrada.');
        return $page;
    }

    private function normaliseUploads(array $uploads): array
    {
        if (!isset($uploads['tmp_name']) || !is_array($uploads['tmp_name'])) return [];
        $result = [];
        foreach ($uploads['tmp_name'] as $key => $tmp) if (($uploads['error'][$key] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && is_uploaded_file((string) $tmp) && (int) ($uploads['size'][$key] ?? 0) <= 10 * 1024 * 1024) $result[] = ['tmp_name' => (string) $tmp];
        return $result;
    }
}
