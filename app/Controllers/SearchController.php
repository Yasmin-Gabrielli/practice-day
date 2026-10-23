<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Middleware\AuthenticationMiddleware;
use App\Services\SearchService;

final class SearchController extends Controller
{
    public function __construct(private readonly array $config) {}

    private function auth(): void
    {
        try {
            (new AuthenticationMiddleware($this->config))->handle();
        } catch (AuthenticationException) {
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $this->auth();
        $query = trim((string) ($_GET['q'] ?? ''));
        $results = (new SearchService($this->config))->search((string) ($_SESSION['user_id'] ?? ''), $query);
        $this->view('busca/index', [
            'title' => 'Busca | PracticeDay',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'dashboard',
            'query' => $query,
            'results' => $results,
        ]);
    }
}