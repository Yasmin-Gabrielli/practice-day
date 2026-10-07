<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Middleware\AuthenticationMiddleware;
use App\Repositories\DisciplineRepository;
use App\Repositories\ProductivityRepository;

final class PomodoroController extends Controller
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
        $disciplineRepository = new DisciplineRepository($this->config['database']);
        $disciplines = $disciplineRepository->allForUser($this->u());

        $this->view('pomodoro/index', [
            'activePage' => 'pomodoro',
            'title' => 'Pomodoro - PracticeDay',
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'csrfToken' => Csrf::token(),
            'disciplines' => $disciplines,
        ]);
    }

    public function save(): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['inicio'], $data['fim'], $data['duracao'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados incompletos']);
            exit;
        }

        $sessionData = [
            'usuario_id' => $this->u(),
            'disciplina_id' => !empty($data['disciplina_id']) ? $data['disciplina_id'] : null,
            'inicio_sessao' => $data['inicio'],
            'fim_sessao' => $data['fim'],
            'duracao_minutos' => (int) $data['duracao'],
            'tipo_tecnica' => $data['tecnica'] ?? 'pomodoro'
        ];

        try {
            $productivityRepository = new ProductivityRepository($this->config['database']);
            $id = $productivityRepository->createStudySession($sessionData);
            
            // Updates productivity records (tempo_estudo_minutos)
            $productivityRepository->updateDailyStudyTime($this->u(), date('Y-m-d'), (int) $data['duracao']);
            
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar sessão: ' . $e->getMessage()]);
        }
        exit;
    }
}
