<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\FileService;

final class FavoritesController extends Controller
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

    /** GET /favoritos */
    public function index(): void
    {
        $this->auth();

        $search   = isset($_GET['busca']) ? trim((string) $_GET['busca']) : null;
        $viewMode = isset($_GET['view']) && in_array($_GET['view'], ['cards', 'lista'], true)
            ? (string) $_GET['view']
            : 'cards';

        $service = $this->s();
        $files   = $service->favorites($this->u(), $search ?: null);
        $stats   = $service->stats($this->u());

        $this->view('arquivos/favoritos', [
            'title'         => 'Favoritos | PracticeDay',
            'csrfToken'     => Csrf::token(),
            'userName'      => $_SESSION['user_name'] ?? '',
            'userAvatar'    => $_SESSION['user_avatar'] ?? null,
            'activePage'    => 'favoritos',
            'feedback'      => Flash::get('favorites'),
            'files'         => $files,
            'stats'         => $stats,
            'currentView'   => $viewMode,
            'currentSearch' => $search ?? '',
        ]);
    }

    /** POST /favoritos/{id}/remover */
    public function remove(string $id): never
    {
        $this->auth();

        try {
            Csrf::validate($_POST);
            $this->s()->favorite($this->u(), $id, false);
            Flash::set('favorites', ['success' => 'Arquivo removido dos favoritos.']);
        } catch (\Throwable $e) {
            Flash::set('favorites', ['errors' => ['favorito' => $e->getMessage()]]);
        }

        $this->redirect('/favoritos');
    }
}
