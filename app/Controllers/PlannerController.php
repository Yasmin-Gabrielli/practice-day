<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\PlannerService;

final class PlannerController extends Controller
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

    private function s(): PlannerService
    {
        return new PlannerService($this->config);
    }

    public function index(): void
    {
        $this->auth();

        $status = isset($_GET['status']) ? (string) $_GET['status'] : null;
        $priority = isset($_GET['prioridade']) ? (string) $_GET['prioridade'] : null;
        $disciplineId = isset($_GET['disciplina']) ? (string) $_GET['disciplina'] : null;

        $service = $this->s();
        $tasks = $service->list($this->u(), $status, $priority, $disciplineId);
        $disciplines = $service->getDisciplines($this->u());
        $files = $service->getFiles($this->u());

        $this->view('planner/index', [
            'title' => 'Planner | PracticeDay',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'planner',
            'feedback' => Flash::get('planner'),
            'tasks' => $tasks,
            'disciplines' => $disciplines,
            'files' => $files,
            'currentStatus' => $status,
            'currentPriority' => $priority,
            'currentDiscipline' => $disciplineId,
        ]);
    }

    public function create(): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->create($this->u(), $_POST);
            Flash::set('planner', ['success' => 'Tarefa criada com sucesso.']);
        } catch (ValidationException $e) {
            Flash::set('planner', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function update(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->update($this->u(), $id, $_POST);
            Flash::set('planner', ['success' => 'Tarefa atualizada.']);
        } catch (ValidationException $e) {
            Flash::set('planner', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function delete(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->delete($this->u(), $id);
            Flash::set('planner', ['success' => 'Tarefa excluída.']);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function updateStatus(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->updateStatus($this->u(), $id, (string) ($_POST['status'] ?? ''));
            Flash::set('planner', ['success' => 'Status da tarefa atualizado.']);
        } catch (ValidationException $e) {
            Flash::set('planner', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function viewTask(string $id): void
    {
        $this->auth();
        try {
            $task = $this->s()->find($this->u(), $id);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'task' => $task]);
        } catch (\Throwable $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addChecklist(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->addChecklistItem($this->u(), $id, (string) ($_POST['descricao'] ?? ''));
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function toggleChecklist(string $id, string $itemId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $concluido = isset($_POST['concluido']) && $_POST['concluido'] === '1' ? 1 : 0;
            $this->s()->toggleChecklistItem($this->u(), $id, $itemId, $concluido);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function deleteChecklist(string $id, string $itemId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->deleteChecklistItem($this->u(), $id, $itemId);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function attachFile(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->addAttachment($this->u(), $id, (string) ($_POST['arquivo_id'] ?? ''));
            Flash::set('planner', ['success' => 'Arquivo anexado.']);
        } catch (ValidationException $e) {
            Flash::set('planner', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }

    public function detachFile(string $id, string $attachmentId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->removeAttachment($this->u(), $id, $attachmentId);
            Flash::set('planner', ['success' => 'Anexo removido.']);
        } catch (\Throwable $e) {
            Flash::set('planner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/planner');
    }
}
