<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Middleware\AuthenticationMiddleware;
use App\Repositories\DisciplineRepository;
use App\Repositories\ProductivityRepository;

final class StatisticsController extends Controller
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

    public function index(): void
    {
        $this->auth();
        $userId = $this->u();
        
        $filter = $_GET['filter'] ?? 'mes'; // dia, semana, mes
        $disciplineId = !empty($_GET['disciplina']) ? $_GET['disciplina'] : null;
        
        $disciplineRepository = new DisciplineRepository($this->config['database']);
        $productivityRepository = new ProductivityRepository($this->config['database']);
        
        $disciplines = $disciplineRepository->allForUser($userId);
        $stats = $productivityRepository->getStatistics($userId, $filter, $disciplineId);

        $this->view('estatisticas/index', [
            'activePage' => 'estatisticas',
            'title' => 'Estatísticas - PracticeDay',
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'csrfToken' => Csrf::token(),
            'disciplines' => $disciplines,
            'stats' => $stats,
            'currentFilter' => $filter,
            'currentDiscipline' => $disciplineId
        ]);
    }
}
