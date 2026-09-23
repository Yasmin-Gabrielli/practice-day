<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Middleware\AuthenticationMiddleware;
use App\Services\DashboardService;

final class DashboardController extends Controller
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    public function index(): void
    {
        try {
            (new AuthenticationMiddleware($this->config))->handle();
        } catch (AuthenticationException) {
            $this->redirect('/login');
        }

        $dashboard = (new DashboardService($this->config))->forUser((string) $_SESSION['user_id']);

        $this->view('dashboard/index', [
            'title' => 'Dashboard | PracticeDay',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'dashboard',
            'dashboard' => $dashboard,
        ]);
    }
}
