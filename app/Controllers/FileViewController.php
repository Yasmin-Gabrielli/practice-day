<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Helpers\Csrf;
use App\Helpers\Url;
use App\Middleware\AuthenticationMiddleware;
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
    public function view(string $id): void
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

        $txtContent = null;
        if ($viewerType === 'txt') {
            try {
                $txtContent = $service->readTxtContent($this->u(), $id);
            } catch (\Throwable) {
                $txtContent = null;
            }
        }

        $this->view('arquivos/view', [
            'title'       => htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') . ' | Visualizador | PracticeDay',
            'csrfToken'   => Csrf::token(),
            'userName'    => $_SESSION['user_name'] ?? '',
            'userAvatar'  => $_SESSION['user_avatar'] ?? null,
            'activePage'  => 'arquivos',
            'file'        => $file,
            'viewerType'  => $viewerType,
            'txtContent'  => $txtContent,
            'serveUrl'    => Url::to('/arquivos/' . rawurlencode($id) . '/servir'),
            'downloadUrl' => Url::to('/arquivos/' . rawurlencode($id) . '/download'),
            'backUrl'     => Url::to('/arquivos'),
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

        $isInline = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true);

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
        ]);
    }
}
