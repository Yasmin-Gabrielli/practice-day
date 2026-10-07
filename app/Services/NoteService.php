<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Uuid;
use App\Repositories\DisciplineRepository;
use App\Repositories\NoteRepository;

final class NoteService extends Service
{
    private NoteRepository $notes;
    private DisciplineRepository $disciplines;

    public function __construct(private readonly array $config)
    {
        $this->notes = new NoteRepository($config['database']);
        $this->disciplines = new DisciplineRepository($config['database']);
    }

    public function list(string $userId, ?string $search, ?string $disciplineId): array { return $this->notes->list($userId, $search, $disciplineId); }
    public function recent(string $userId): array { return $this->notes->recent($userId); }
    public function disciplines(string $userId): array { return $this->disciplines->allForUser($userId); }
    public function find(string $userId, string $id): array { return $this->noteOrFail($userId, $id); }

    public function create(string $userId, array $data): void
    {
        $note = $this->validate($userId, $data);
        $this->notes->create(Uuid::v4(), $userId, $note['disciplina_id'], $note['titulo'], $note['conteudo'], $note['blocos']);
    }

    public function update(string $userId, string $id, array $data): void
    {
        $this->noteOrFail($userId, $id);
        $note = $this->validate($userId, $data);
        $this->notes->update($id, $userId, $note['disciplina_id'], $note['titulo'], $note['conteudo'], $note['blocos']);
    }

    public function delete(string $userId, string $id): void
    {
        $this->noteOrFail($userId, $id);
        $this->notes->delete($userId, $id);
    }

    private function noteOrFail(string $userId, string $id): array
    {
        $note = $this->notes->find($userId, $id);
        if ($note === null) throw new NotFoundException('Nota não encontrada.');
        return $note;
    }

    private function validate(string $userId, array $data): array
    {
        $title = trim((string) ($data['titulo'] ?? ''));
        $content = trim((string) ($data['conteudo'] ?? ''));
        $disciplineId = trim((string) ($data['disciplina_id'] ?? ''));
        $errors = [];
        if ($title === '') $errors['titulo'] = 'O título é obrigatório.';
        if (mb_strlen($title) > 255) $errors['titulo'] = 'O título deve ter no máximo 255 caracteres.';
        if ($disciplineId !== '' && $this->disciplines->findForUser($disciplineId, $userId) === null) $errors['disciplina_id'] = 'Disciplina inválida.';
        $types = ['texto', 'codigo', 'imagem', 'lista', 'equacao'];
        $blocks = [];
        foreach ((array) ($data['blocos_tipo'] ?? []) as $index => $type) {
            $type = (string) $type;
            $blockContent = trim((string) (($data['blocos_conteudo'] ?? [])[$index] ?? ''));
            if ($blockContent === '') continue;
            if (!in_array($type, $types, true)) { $errors['blocos'] = 'Tipo de bloco inválido.'; continue; }
            $blocks[] = ['tipo' => $type, 'conteudo' => $blockContent];
        }
        if ($content === '' && $blocks === []) $errors['conteudo'] = 'Adicione conteúdo ou pelo menos um bloco à nota.';
        if ($errors !== []) throw new ValidationException($errors);
        return ['titulo' => $title, 'disciplina_id' => $disciplineId === '' ? null : $disciplineId, 'conteudo' => $content === '' ? null : $content, 'blocos' => $blocks];
    }
}
