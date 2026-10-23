<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\JsonResponse;
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

    /** Detecta chamadas AJAX para responder JSON em vez de redirecionar. */
    private function expectsJson(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
    }

    /** Responde sucesso em JSON quando a requisição é AJAX; senão flash + redirect. */
    private function ok(string $message, array $extra = []): never
    {
        if ($this->expectsJson()) {
            JsonResponse::send(['success' => true, 'message' => $message] + $extra);
        }

        Flash::set('planner', ['success' => $message]);
        $this->redirect('/planner');
    }

    /** Responde falha em JSON quando a requisição é AJAX; senão flash + redirect. */
    private function fail(array $errors): never
    {
        $errors = $errors === [] ? ['geral' => 'Não foi possível concluir a operação.'] : $errors;

        if ($this->expectsJson()) {
            JsonResponse::send(['success' => false, 'errors' => $errors], 422);
        }

        Flash::set('planner', ['errors' => $errors]);
        $this->redirect('/planner');
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
            $this->ok('Status da tarefa atualizado.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
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
            $this->ok('Item adicionado à checklist.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }

    public function toggleChecklist(string $id, string $itemId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $concluido = isset($_POST['concluido']) && $_POST['concluido'] === '1' ? 1 : 0;
            $this->s()->toggleChecklistItem($this->u(), $id, $itemId, $concluido);
            $this->ok('Checklist atualizada.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }

    public function deleteChecklist(string $id, string $itemId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->deleteChecklistItem($this->u(), $id, $itemId);
            $this->ok('Item removido da checklist.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }

    public function attachFile(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->addAttachment($this->u(), $id, (string) ($_POST['arquivo_id'] ?? ''));
            $this->ok('Arquivo anexado.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }

    /**
     * Upload direto de um arquivo a partir do modal da tarefa.
     * O arquivo é salvo em "Meus Arquivos" e anexado automaticamente.
     */
    public function attachUpload(string $id): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->attachUpload($this->u(), $id, $_FILES['arquivo'] ?? []);
            $this->ok('Arquivo enviado e anexado à tarefa.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }

    public function detachFile(string $id, string $attachmentId): never
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->s()->removeAttachment($this->u(), $id, $attachmentId);
            $this->ok('Anexo removido.', ['task' => $this->s()->find($this->u(), $id)]);
        } catch (ValidationException $e) {
            $this->fail($e->errors());
        } catch (\Throwable $e) {
            $this->fail(['geral' => $e->getMessage()]);
        }
    }
}
