<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\NoteService;

final class NoteController extends Controller
{
    public function __construct(private readonly array $config) {}

    public function index(): void
    {
        $this->auth(); $service = $this->service();
        $search = trim((string) ($_GET['pesquisa'] ?? ''));
        $discipline = trim((string) ($_GET['disciplina'] ?? ''));
        $this->view('notas/index', ['title' => 'Notas Rápidas | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'notas', 'notes' => $service->list($this->user(), $search, $discipline), 'recent' => $service->recent($this->user()), 'disciplines' => $service->disciplines($this->user()), 'search' => $search, 'currentDiscipline' => $discipline, 'feedback' => Flash::get('note')]);
    }

    public function newForm(): void { $this->form(null); }
    public function editForm(string $id): void
    {
        $this->auth();
        $this->form($this->service()->find($this->user(), $id));
    }

    public function create(): never
    {
        $this->auth();
        try { Csrf::validate($_POST); $this->service()->create($this->user(), $_POST); Flash::set('note', ['success' => 'Nota criada com sucesso.']); $this->redirect('/notas'); }
        catch (ValidationException $e) { Flash::set('note_form', ['errors' => $e->errors(), 'old' => $_POST]); $this->redirect('/notas/nova'); }
    }

    public function update(string $id): never
    {
        $this->auth();
        try { Csrf::validate($_POST); $this->service()->update($this->user(), $id, $_POST); Flash::set('note', ['success' => 'Nota atualizada.']); $this->redirect('/notas'); }
        catch (ValidationException $e) { Flash::set('note_form', ['errors' => $e->errors(), 'old' => $_POST]); $this->redirect('/notas/' . rawurlencode($id) . '/editar'); }
    }

    public function delete(string $id): never
    {
        $this->auth(); Csrf::validate($_POST); $this->service()->delete($this->user(), $id); Flash::set('note', ['success' => 'Nota excluída.']); $this->redirect('/notas');
    }

    private function form(?array $note): void
    {
        $this->auth(); $feedback = Flash::get('note_form');
        if ($note === null && isset($feedback['old'])) $note = $this->oldToNote($feedback['old']);
        $this->view('notas/form', ['title' => ($note === null ? 'Nova Nota' : 'Editar Nota') . ' | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'notas', 'note' => $note, 'disciplines' => $this->service()->disciplines($this->user()), 'feedback' => $feedback]);
    }

    private function oldToNote(array $old): array
    {
        $blocks = [];
        foreach ((array) ($old['blocos_tipo'] ?? []) as $i => $type) $blocks[] = ['tipo_bloco' => (string) $type, 'conteudo' => (string) (($old['blocos_conteudo'] ?? [])[$i] ?? '')];
        return ['titulo' => (string) ($old['titulo'] ?? ''), 'disciplina_id' => (string) ($old['disciplina_id'] ?? ''), 'conteudo' => (string) ($old['conteudo'] ?? ''), 'blocos' => $blocks];
    }
    private function service(): NoteService { return new NoteService($this->config); }
    private function user(): string { return (string) ($_SESSION['user_id'] ?? ''); }
    private function auth(): void { try { (new AuthenticationMiddleware($this->config))->handle(); } catch (AuthenticationException) { $this->redirect('/login'); } }
}
