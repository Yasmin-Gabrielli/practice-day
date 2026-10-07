<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\FileViewerService;

final class LibraryController extends Controller
{
    public function __construct(private readonly array $config) {}

    public function index(): void
    {
        $this->auth();
        $data = $this->service()->shelf($this->user());
        $this->view('biblioteca/index', ['title' => 'Biblioteca | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'biblioteca', 'feedback' => Flash::get('library'), ...$data]);
    }

    public function add(string $fileId): never
    {
        $this->auth();
        try { Csrf::validate($_POST); $this->service()->addToShelf($this->user(), $fileId); Flash::set('library', ['success' => 'Arquivo adicionado à estante.']); }
        catch (ValidationException $e) { Flash::set('library', ['errors' => $e->errors()]); }
        catch (\Throwable $e) { Flash::set('library', ['errors' => ['arquivo' => $e->getMessage()]]); }
        $this->redirect('/biblioteca');
    }

    private function service(): FileViewerService { return new FileViewerService($this->config); }
    private function user(): string { return (string) ($_SESSION['user_id'] ?? ''); }
    private function auth(): void { try { (new AuthenticationMiddleware($this->config))->handle(); } catch (AuthenticationException) { $this->redirect('/login'); } }
}
