<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\DisciplineService;
use App\Validators\DisciplineValidator;

final class DisciplineController extends Controller
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config) {}

    public function index(): void
    {
        $this->requireAuthentication();
        $this->view('disciplinas/index', [
            'title' => 'Disciplinas | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'activePage' => 'disciplinas', 'disciplines' => $this->service()->list($this->userId()), 'icons' => DisciplineValidator::icons(), 'feedback' => Flash::get('discipline'),
        ]);
    }

    public function create(): never
    {
        $this->requireAuthentication();
        try {
            Csrf::validate($_POST);
            $this->service()->create($this->userId(), (new DisciplineValidator())->validate($_POST));
            Flash::set('discipline', ['success' => 'Disciplina criada com sucesso.']);
        } catch (ValidationException $exception) {
            Flash::set('discipline', ['errors' => $exception->errors(), 'old' => ['nome' => trim((string) ($_POST['nome'] ?? '')), 'cor' => trim((string) ($_POST['cor'] ?? '')), 'icone' => trim((string) ($_POST['icone'] ?? ''))]]);
        }
        $this->redirect('/disciplinas');
    }

    public function show(string $id): void
    {
        $this->requireAuthentication();
        $detail = $this->service()->detail($id, $this->userId());
        $this->view('disciplinas/show', ['title' => $detail['discipline']['nome'] . ' | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'disciplinas', ...$detail]);
    }

    public function editForm(string $id): void
    {
        $this->requireAuthentication();
        $this->view('disciplinas/edit', ['title' => 'Editar disciplina | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'disciplinas', 'discipline' => $this->service()->find($id, $this->userId()), 'icons' => DisciplineValidator::icons(), 'feedback' => Flash::get('discipline_edit')]);
    }

    public function update(string $id): never
    {
        $this->requireAuthentication();
        try {
            Csrf::validate($_POST);
            $this->service()->update($id, $this->userId(), (new DisciplineValidator())->validate($_POST));
            Flash::set('discipline', ['success' => 'Disciplina atualizada com sucesso.']);
            $this->redirect('/disciplinas/' . rawurlencode($id));
        } catch (ValidationException $exception) {
            Flash::set('discipline_edit', ['errors' => $exception->errors(), 'old' => ['nome' => trim((string) ($_POST['nome'] ?? '')), 'cor' => trim((string) ($_POST['cor'] ?? '')), 'icone' => trim((string) ($_POST['icone'] ?? ''))]]);
            $this->redirect('/disciplinas/' . rawurlencode($id) . '/editar');
        }
    }

    public function delete(string $id): never
    {
        $this->requireAuthentication();
        Csrf::validate($_POST);
        $this->service()->delete($id, $this->userId());
        Flash::set('discipline', ['success' => 'Disciplina excluída. Arquivos, tarefas e notas relacionados permaneceram sem disciplina, conforme as regras do banco.']);
        $this->redirect('/disciplinas');
    }

    private function service(): DisciplineService { return new DisciplineService($this->config); }
    private function userId(): string { return (string) $_SESSION['user_id']; }
    private function requireAuthentication(): void
    {
        try { (new AuthenticationMiddleware($this->config))->handle(); } catch (\App\Exceptions\AuthenticationException) { $this->redirect('/login'); }
    }
}
