<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Helpers\Csrf;
use App\Helpers\Url;
use App\Middleware\AuthenticationMiddleware;
use App\Repositories\FileViewerRepository;
use App\Services\FileViewerService;

final class FileViewController extends Controller
{
    public function __construct(private readonly array $config)
    {
    }

    private function auth(): void
    {
        try {
            (new AuthenticationMiddleware($this->config))->handle();
        } catch (AuthenticationException) {
            $this->redirect('/login');
        }
    }

    private function u(): string
    {
        return (string) ($_SESSION['user_id'] ?? '');
    }

    /**
     * GET /arquivos/visualizar/{id}
     * Pagina principal de visualizacao de arquivo.
     */
    public function show(string $id): void
    {
        $this->auth();

        $service = new FileViewerService($this->config);

        try {
            $file = $service->getViewableFile($this->u(), $id);
        } catch (NotFoundException $e) {
            http_response_code(404);
            $this->view('arquivos/view_error', [
                'title'      => 'Arquivo nao encontrado | PracticeDay',
                'csrfToken'  => Csrf::token(),
                'userName'   => $_SESSION['user_name'] ?? '',
                'userAvatar' => $_SESSION['user_avatar'] ?? null,
                'activePage' => 'arquivos',
                'message'    => $e->getMessage(),
                'backUrl'    => Url::to('/arquivos'),
            ]);
            return;
        }

        $ext = strtolower((string) ($file['extensao'] ?? ''));

        if ($ext === 'epub') {
            $this->redirect('/arquivos/visualizar/' . rawurlencode($id) . '/epub');
            return;
        }

        $viewerType = match ($ext) {
            'pdf'                => 'pdf',
            'txt'                => 'txt',
            'png', 'jpg', 'jpeg' => 'image',
            'docx'               => 'docx',
            default              => 'unsupported',
        };
        $libraryData = $viewerType === 'pdf' ? $service->getOrCreateLibraryEntry($this->u(), $id, $file) : null;

        $txtContent = null;
        if ($viewerType === 'txt') {
            try {
                $txtContent = $service->readTxtContent($this->u(), $id);
            } catch (\Throwable) {
                $txtContent = null;
            }
        }

        $docxPreview = null;
        if ($viewerType === 'docx') {
            try {
                $docxPreview = $service->convertDocxToHtml($this->u(), $id);
            } catch (\Throwable $e) {
                $docxPreview = [
                    'html'    => '',
                    'engine'  => 'none',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $this->view('arquivos/view', [
            'title'       => htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') . ' | Visualizador | PracticeDay',
            'csrfToken'   => Csrf::token(),
            'userName'    => $_SESSION['user_name'] ?? '',
            'userAvatar'  => $_SESSION['user_avatar'] ?? null,
            'activePage'  => $viewerType === 'pdf' ? 'biblioteca' : 'arquivos',
            'file'        => $file,
            'viewerType'  => $viewerType,
            'txtContent'  => $txtContent,
            'docxPreview' => $docxPreview,
            'serveUrl'    => Url::to('/arquivos/' . rawurlencode($id) . '/servir'),
            'downloadUrl' => Url::to('/arquivos/' . rawurlencode($id) . '/download'),
            'backUrl'     => Url::to('/arquivos'),
            'library'     => $libraryData,
            'libraryApi'  => $libraryData !== null ? [
                'progresso' => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/progresso'),
                'marcadores' => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/marcadores'),
            ] : [],
        ]);
    }

    /**
     * GET /arquivos/{id}/servir
     * Endpoint seguro que entrega o conteudo do arquivo sem expor o caminho fisico.
     */
    public function serve(string $id): never
    {
        $this->auth();

        $service = new FileViewerService($this->config);

        try {
            $result = $service->serveFile($this->u(), $id);
        } catch (NotFoundException) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Arquivo nao encontrado.';
            exit;
        }

        $file = $result['file'];
        $path = $result['path'];

        if (!file_exists($path) || !is_readable($path)) {
            http_response_code(404);
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $ext  = strtolower((string) ($file['extensao'] ?? ''));
        $mime = (string) ($file['tipo_mime'] ?? 'application/octet-stream');

        $isInline = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'epub'], true);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');

        if ($isInline) {
            header('Content-Disposition: inline');
            header('Cache-Control: private, max-age=3600');
        } else {
            $asciiName = preg_replace('/[^\x20-\x7e]/', '', (string) $file['nome_original']) ?: 'arquivo';
            header('Content-Disposition: attachment; filename="' . addslashes($asciiName) . '"');
            header('Cache-Control: no-store');
        }

        readfile($path);
        exit;
    }

    /**
     * GET /arquivos/visualizar/{id}/epub
     * Leitor de EPUB integrado ao modulo Biblioteca.
     */
    public function viewEpub(string $id): void
    {
        $this->auth();

        $service = new FileViewerService($this->config);

        try {
            $file = $service->getViewableFile($this->u(), $id);
        } catch (NotFoundException $e) {
            http_response_code(404);
            $this->view('arquivos/view_error', [
                'title'      => 'Arquivo nao encontrado | PracticeDay',
                'csrfToken'  => Csrf::token(),
                'userName'   => $_SESSION['user_name'] ?? '',
                'userAvatar' => $_SESSION['user_avatar'] ?? null,
                'activePage' => 'arquivos',
                'message'    => $e->getMessage(),
                'backUrl'    => Url::to('/arquivos'),
            ]);
            return;
        }

        if (strtolower((string) ($file['extensao'] ?? '')) !== 'epub') {
            $this->redirect('/arquivos/visualizar/' . rawurlencode($id));
            return;
        }

        $libraryData = $service->getOrCreateLibraryEntry($this->u(), $id, $file);

        $this->view('arquivos/view_epub', [
            'title'       => htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') . ' | Leitor EPUB | PracticeDay',
            'csrfToken'   => Csrf::token(),
            'userName'    => $_SESSION['user_name'] ?? '',
            'userAvatar'  => $_SESSION['user_avatar'] ?? null,
            'activePage'  => 'arquivos',
            'file'        => $file,
            'library'     => $libraryData,
            'serveUrl'    => Url::to('/arquivos/' . rawurlencode($id) . '/servir'),
            'downloadUrl' => Url::to('/arquivos/' . rawurlencode($id) . '/download'),
            'backUrl'     => Url::to('/arquivos'),
            'libraryApi'  => [
                'progresso'  => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/progresso'),
                'marcadores' => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/marcadores'),
                'destaques'  => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/destaques'),
                'anotacoes'  => Url::to('/arquivos/biblioteca/' . rawurlencode((string) $libraryData['id']) . '/anotacoes'),
            ],
        ]);
    }

    /**
     * POST /arquivos/biblioteca/{libraryId}/progresso
     */
    public function saveProgress(string $libraryId): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
            return;
        }

        try {
            Csrf::validate($_POST);
        } catch (\Throwable) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido.']);
            return;
        }

        $service = new FileViewerService($this->config);

        try {
            $data = $service->saveReadingProgress($this->u(), $libraryId, $_POST);
            echo json_encode(['ok' => true, 'progresso' => $data]);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /arquivos/biblioteca/{libraryId}/marcadores
     */
    public function saveBookmark(string $libraryId): void
    {
        $this->jsonLibraryMutation($libraryId, function (FileViewerService $service, FileViewerRepository $repo) use ($libraryId): array {
            $pagina = (int) ($_POST['numero_pagina'] ?? 0);
            $titulo = isset($_POST['titulo']) ? (string) $_POST['titulo'] : null;
            $action = (string) ($_POST['action'] ?? 'create');

            if ($action === 'delete') {
                $id = (string) ($_POST['id'] ?? '');
                $ok = $repo->deleteBookmark($libraryId, $this->u(), $id);

                return ['ok' => $ok, 'marcadores' => $repo->getBookmarks($libraryId, $this->u())];
            }

            $item = $repo->addBookmark($libraryId, $this->u(), $pagina, $titulo);
            if ($item === null) {
                throw new NotFoundException('Não foi possível salvar o marcador.');
            }

            return ['ok' => true, 'marcador' => $item, 'marcadores' => $repo->getBookmarks($libraryId, $this->u())];
        });
    }

    /**
     * POST /arquivos/biblioteca/{libraryId}/destaques
     */
    public function saveHighlight(string $libraryId): void
    {
        $this->jsonLibraryMutation($libraryId, function (FileViewerService $service, FileViewerRepository $repo) use ($libraryId): array {
            $action = (string) ($_POST['action'] ?? 'create');

            if ($action === 'delete') {
                $id = (string) ($_POST['id'] ?? '');
                $ok = $repo->deleteHighlight($libraryId, $this->u(), $id);

                return ['ok' => $ok, 'destaques' => $repo->getHighlights($libraryId, $this->u())];
            }

            $pagina = (int) ($_POST['numero_pagina'] ?? 0);
            $texto  = trim((string) ($_POST['texto_selecionado'] ?? ''));
            $cor    = (string) ($_POST['cor'] ?? '#FFFF00');

            if ($texto === '') {
                throw new NotFoundException('Texto do destaque vazio.');
            }

            $item = $repo->addHighlight($libraryId, $this->u(), $pagina, $texto, $cor);
            if ($item === null) {
                throw new NotFoundException('Não foi possível salvar o destaque.');
            }

            return ['ok' => true, 'destaque' => $item, 'destaques' => $repo->getHighlights($libraryId, $this->u())];
        });
    }

    /**
     * POST /arquivos/biblioteca/{libraryId}/anotacoes
     */
    public function saveNote(string $libraryId): void
    {
        $this->jsonLibraryMutation($libraryId, function (FileViewerService $service, FileViewerRepository $repo) use ($libraryId): array {
            $action = (string) ($_POST['action'] ?? 'create');

            if ($action === 'delete') {
                $id = (string) ($_POST['id'] ?? '');
                $ok = $repo->deleteNote($libraryId, $this->u(), $id);

                return ['ok' => $ok, 'anotacoes' => $repo->getNotes($libraryId, $this->u())];
            }

            $destaqueId = (string) ($_POST['destaque_id'] ?? '');
            $conteudo   = trim((string) ($_POST['conteudo'] ?? ''));

            if ($destaqueId === '' || $conteudo === '') {
                throw new NotFoundException('Destaque e conteúdo são obrigatórios.');
            }

            $item = $repo->addNote($libraryId, $this->u(), $destaqueId, $conteudo);
            if ($item === null) {
                throw new NotFoundException('Não foi possível salvar a anotação.');
            }

            return ['ok' => true, 'anotacao' => $item, 'anotacoes' => $repo->getNotes($libraryId, $this->u())];
        });
    }

    /**
     * @param callable(FileViewerService, \App\Repositories\FileViewerRepository): array<string,mixed> $callback
     */
    private function jsonLibraryMutation(string $libraryId, callable $callback): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');

        try {
            Csrf::validate($_POST);
        } catch (\Throwable) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido.']);
            return;
        }

        $service = new FileViewerService($this->config);
        $repo    = new FileViewerRepository($this->config['database']);

        try {
            $service->assertLibraryAccess($this->u(), $libraryId);
            $payload = $callback($service, $repo);
            echo json_encode($payload);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
