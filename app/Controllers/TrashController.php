<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\FileService;

final class TrashController extends Controller
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

    /** GET /lixeira */
    public function index(): void
    {
        $this->auth();

        $search = isset($_GET['busca']) ? trim((string) $_GET['busca']) : null;

        $service = $this->s();
        $files   = $service->trashListing($this->u(), $search ?: null);
        $stats   = $service->stats($this->u());

        $this->view('arquivos/lixeira', [
            'title'         => 'Lixeira | PracticeDay',
            'csrfToken'     => Csrf::token(),
            'userName'      => $_SESSION['user_name'] ?? '',
            'userAvatar'    => $_SESSION['user_avatar'] ?? null,
            'activePage'    => 'lixeira',
            'feedback'      => Flash::get('trash'),
            'files'         => $files,
            'stats'         => $stats,
            'currentSearch' => $search ?? '',
        ]);
    }

    /** POST /lixeira/{id}/restaurar */
    public function restore(string $id): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $this->s()->restoreFromTrash($this->u(), $id);
            Flash::set('trash', ['success' => 'Arquivo restaurado com sucesso.']);
        } catch (\Throwable $e) {
            Flash::set('trash', ['errors' => ['restaurar' => $e->getMessage()]]);
        }

        $this->redirect('/lixeira');
    }

    /** POST /lixeira/{id}/destruir */
    public function destroy(string $id): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $this->s()->deletePermanently($this->u(), $id);
            Flash::set('trash', ['success' => 'Arquivo excluido definitivamente do servidor.']);
        } catch (\Throwable $e) {
            Flash::set('trash', ['errors' => ['destruir' => $e->getMessage()]]);
        }

        $this->redirect('/lixeira');
    }

    /** POST /lixeira/esvaziar */
    public function empty(): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $count = $this->s()->emptyTrash($this->u());
            Flash::set('trash', ['success' => $count > 0
                ? $count . ' arquivo(s) excluido(s) definitivamente.'
                : 'A lixeira ja estava vazia.']);
        } catch (\Throwable $e) {
            Flash::set('trash', ['errors' => ['esvaziar' => $e->getMessage()]]);
        }

        $this->redirect('/lixeira');
    }
}
