<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\CalendarService;

final class CalendarController extends Controller
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

    private function s(): CalendarService
    {
        return new CalendarService($this->config);
    }

    public function index(): void
    {
        $this->auth();
        $service = $this->s();
        $disciplines = $service->getDisciplines($this->u());
        $upcoming = $service->getUpcomingEvents($this->u(), 10);

        $this->view('calendario/index', [
            'title' => 'Calendário | PracticeDay',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'calendario',
            'feedback' => Flash::get('calendario'),
            'disciplines' => $disciplines,
            'upcoming' => $upcoming,
        ]);
    }

    public function fetchEvents(): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $start = isset($_GET['start']) ? (string)$_GET['start'] : date('Y-m-01');
            $end = isset($_GET['end']) ? (string)$_GET['end'] : date('Y-m-t');

            $service = $this->s();
            $events = $service->getEvents($this->u(), $start, $end);
            $tasks = $service->getTasksAsEvents($this->u(), $start, $end);

            $out = [];
            foreach ($events as $e) {
                $out[] = [
                    'id' => 'evt_' . $e['id'],
                    'real_id' => $e['id'],
                    'title' => $e['titulo'],
                    'start' => $e['data_inicio'],
                    'end' => $e['data_fim'],
                    'color' => $e['disciplina_cor'] ?? '#4f46e5',
                    'tipo' => $e['tipo'],
                    'is_task' => false
                ];
            }
            foreach ($tasks as $t) {
                $out[] = [
                    'id' => 'tsk_' . $t['id'],
                    'real_id' => $t['id'],
                    'title' => '[Tarefa] ' . $t['titulo'],
                    'start' => $t['data_vencimento'],
                    'end' => $t['data_vencimento'],
                    'color' => $t['disciplina_cor'] ?? '#6c757d',
                    'tipo' => 'tarefa',
                    'is_task' => true
                ];
            }

            echo json_encode($out);
        } catch (\Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function create(): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->createEvent($this->u(), $_POST);
            Flash::set('calendario', ['success' => 'Evento criado.']);
        } catch (ValidationException $e) {
            Flash::set('calendario', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('calendario', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/calendario');
    }

    public function update(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->updateEvent($this->u(), $id, $_POST);
            Flash::set('calendario', ['success' => 'Evento atualizado.']);
        } catch (ValidationException $e) {
            Flash::set('calendario', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('calendario', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/calendario');
    }

    public function delete(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->deleteEvent($this->u(), $id);
            Flash::set('calendario', ['success' => 'Evento excluído.']);
        } catch (\Throwable $e) {
            Flash::set('calendario', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/calendario');
    }

    public function viewEvent(string $id): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $event = $this->s()->findEvent($this->u(), $id);
            echo json_encode(['success' => true, 'event' => $event]);
        } catch (\Throwable $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function viewTask(string $id): void
    {
        $this->auth();
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode(['success' => true, 'event' => $this->s()->findTaskAsEvent($this->u(), $id)]);
        } catch (\Throwable $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
