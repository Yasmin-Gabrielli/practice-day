<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\Url;
use App\Middleware\AuthenticationMiddleware;
use App\Services\FileService;

final class FileController extends Controller
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

    private function s(): FileService
    {
        return new FileService($this->config);
    }

    public function index(): void
    {
        $this->auth();

        $folder = isset($_GET['pasta']) ? (string) $_GET['pasta'] : null;
        $filter = isset($_GET['filtro']) ? (string) $_GET['filtro'] : 'todos';
        $discipline = isset($_GET['disciplina']) ? (string) $_GET['disciplina'] : null;
        $search = isset($_GET['busca']) ? (string) $_GET['busca'] : null;
        $view = isset($_GET['view']) && in_array($_GET['view'], ['cards', 'lista'], true)
            ? (string) $_GET['view']
            : 'cards';

        try {
            $data = $this->s()->listing($this->u(), $folder, $filter, $discipline, $search);
        } catch (NotFoundException $e) {
            Flash::set('files', ['errors' => ['pasta' => $e->getMessage()]]);
            $this->redirect('/arquivos');
        }

        $this->view('arquivos/index', [
            'title' => 'Meus Arquivos | PracticeDay',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'arquivos',
            'feedback' => Flash::get('files'),
            'currentView' => $view,
            ...$data,
        ]);
    }

    public function upload(): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->upload(
                $this->u(),
                $_FILES['arquivo'] ?? [],
                (string) ($_POST['disciplina_id'] ?? ''),
                !empty($_POST['pasta_id']) ? (string) $_POST['pasta_id'] : null,
                (array) ($_POST['tags'] ?? [])
            );
            Flash::set('files', ['success' => 'Arquivo enviado com sucesso para o seu espaço seguro.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['arquivo' => 'Ocorreu um erro no upload: ' . $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function createFolder(): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->createFolder(
                $this->u(),
                (string) ($_POST['nome'] ?? ''),
                !empty($_POST['disciplina_id']) ? (string) $_POST['disciplina_id'] : null,
                !empty($_POST['pasta_pai_id']) ? (string) $_POST['pasta_pai_id'] : null
            );
            Flash::set('files', ['success' => 'Pasta criada com sucesso.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['pasta' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function renameFolder(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->renameFolder($this->u(), $id, (string) ($_POST['nome'] ?? ''));
            Flash::set('files', ['success' => 'Pasta renomeada com sucesso.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['pasta' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function moveFolder(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->moveFolder(
                $this->u(),
                $id,
                !empty($_POST['pasta_pai_id']) ? (string) $_POST['pasta_pai_id'] : null
            );
            Flash::set('files', ['success' => 'Pasta movida com sucesso.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['pasta' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function deleteFolder(string $id): never
    {
        $this->auth();
        $redirectUrl = '/arquivos';

        try {
            Csrf::validate($_POST);
            $this->s()->deleteFolder($this->u(), $id);
            Flash::set('files', ['success' => 'Pasta excluída. Os arquivos foram mantidos na raiz.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['pasta' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function rename(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->rename($this->u(), $id, (string) ($_POST['nome'] ?? ''));
            Flash::set('files', ['success' => 'Arquivo renomeado com sucesso e registrado no histórico.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['nome' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function favorite(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $on = ((string) ($_POST['favorito'] ?? '')) === '1';
            $this->s()->favorite($this->u(), $id, $on);
            Flash::set('files', ['success' => $on ? 'Arquivo adicionado aos favoritos.' : 'Arquivo removido dos favoritos.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['favorito' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function moveFile(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->moveFile(
                $this->u(),
                $id,
                !empty($_POST['pasta_id']) ? (string) $_POST['pasta_id'] : null,
                !empty($_POST['disciplina_id']) ? (string) $_POST['disciplina_id'] : null
            );
            Flash::set('files', ['success' => 'Arquivo movido com sucesso e registrado no histórico.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['mover' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function updateTags(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->updateTags($this->u(), $id, (array) ($_POST['tags'] ?? []));
            Flash::set('files', ['success' => 'Tags do arquivo atualizadas com sucesso.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['tags' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function moveToTrash(string $id): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->moveToTrash($this->u(), $id);
            Flash::set('files', ['success' => 'Arquivo movido para a lixeira. Você poderá restaurá-lo a qualquer momento.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['lixeira' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function restoreFromTrash(string $id): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $this->s()->restoreFromTrash($this->u(), $id);
            Flash::set('files', ['success' => 'Arquivo restaurado com sucesso para a listagem principal.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['restaurar' => $e->getMessage()]]);
        }

        $this->redirect('/arquivos?filtro=lixeira');
    }

    public function deletePermanently(string $id): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $this->s()->deletePermanently($this->u(), $id);
            Flash::set('files', ['success' => 'Arquivo excluído definitivamente do servidor.']);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['destruir' => $e->getMessage()]]);
        }

        $this->redirect('/arquivos?filtro=lixeira');
    }

    public function history(string $id): void
    {
        $this->auth();

        try {
            $file = $this->s()->file($this->u(), $id);
            $history = $this->s()->fileHistory($this->u(), $id);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'file' => [
                    'id' => $file['id'],
                    'nome_original' => $file['nome_original'],
                    'extensao' => $file['extensao'],
                    'tamanho_bytes' => $file['tamanho_bytes'],
                    'disciplina_nome' => $file['disciplina_nome'] ?? 'Sem disciplina',
                    'pasta_nome' => $file['pasta_nome'] ?? 'Sem pasta',
                    'criado_em' => $file['criado_em'],
                ],
                'history' => $history,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            header('Content-Type: application/json; charset=utf-8', true, 404);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function createTag(): never
    {
        $this->auth();
        $redirectUrl = $this->buildRedirectUrl();

        try {
            Csrf::validate($_POST);
            $this->s()->createTag(
                $this->u(),
                (string) ($_POST['nome'] ?? ''),
                (string) ($_POST['cor'] ?? '#2563eb')
            );
            Flash::set('files', ['success' => 'Tag criada com sucesso.']);
        } catch (ValidationException $e) {
            Flash::set('files', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('files', ['errors' => ['tag' => $e->getMessage()]]);
        }

        $this->redirect($redirectUrl);
    }

    public function download(string $id): never
    {
        $this->auth();

        $dl = $this->s()->download($this->u(), $id);
        $file = $dl['file'];
        $path = $dl['path'];

        if (!file_exists($path) || !is_readable($path)) {
            throw new NotFoundException('Arquivo indisponível no servidor.');
        }

        // Limpa buffers de saída anteriores
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = (string) $file['nome_original'];
        $asciiFilename = preg_replace('/[^\x20-\x7e]/', '', $filename) ?: 'download';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($file['tipo_mime'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($asciiFilename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . (string) filesize($path));

        readfile($path);
        exit;
    }

    private function buildRedirectUrl(): string
    {
        $parts = [];
        if (!empty($_POST['redirect_pasta'])) {
            $parts['pasta'] = (string) $_POST['redirect_pasta'];
        }
        if (!empty($_POST['redirect_filtro']) && $_POST['redirect_filtro'] !== 'todos') {
            $parts['filtro'] = (string) $_POST['redirect_filtro'];
        }
        if (!empty($_POST['redirect_view']) && $_POST['redirect_view'] !== 'cards') {
            $parts['view'] = (string) $_POST['redirect_view'];
        }

        return '/arquivos' . ($parts !== [] ? '?' . http_build_query($parts) : '');
    }
}
