<?php

declare (strict_types = 1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Uuid;
use App\Repositories\DisciplineRepository;
use App\Repositories\FileRepository;
use App\Repositories\PlannerRepository;

final class PlannerService extends Service
{
    private PlannerRepository $repo;
    private DisciplineRepository $disciplineRepo;
    private FileRepository $fileRepo;

    public function __construct(private readonly array $config)
    {
        $this->repo           = new PlannerRepository($config['database']);
        $this->disciplineRepo = new DisciplineRepository($config['database']);
        $this->fileRepo       = new FileRepository($config['database']);
    }

    public function list(string $userId, ?string $status = null, ?string $priority = null, ?string $disciplineId = null): array
    {
        return $this->repo->list($userId, $status, $priority, $disciplineId);
    }

    public function find(string $userId, string $id): array
    {
        $task = $this->repo->find($userId, $id);
        if (! $task) {
            throw new NotFoundException('Tarefa não encontrada.');
        }
        return $task;
    }

    public function getDisciplines(string $userId): array
    {
        return $this->disciplineRepo->allForUser($userId);
    }

    public function getFiles(string $userId): array
    {
        return $this->fileRepo->files($userId);
    }

    public function create(string $userId, array $data): void
    {
        $this->validate($data);
        if (! empty($data['disciplina_id']) && ! $this->fileRepo->ownedDiscipline($data['disciplina_id'], $userId)) {
            throw new ValidationException(['disciplina_id' => 'Disciplina inválida.']);
        }

        $id = Uuid::v4();
        $this->repo->create([
            'id'   => $id,
            'u'    => $userId,
            'd'    => ! empty($data['disciplina_id']) ? $data['disciplina_id'] : null,
            't'    => trim($data['titulo']),
            'desc' => ! empty($data['descricao']) ? trim($data['descricao']) : null,
            'p'    => $data['prioridade'] ?? 'media',
            's'    => $data['status'] ?? 'a_fazer',
            'dt'   => ! empty($data['data_vencimento']) ? $data['data_vencimento'] : null,
            'r'    => isset($data['recorrente']) ? 1 : 0,
        ]);
    }

    public function update(string $userId, string $id, array $data): void
    {
        $this->find($userId, $id);
        $this->validate($data);
        if (! empty($data['disciplina_id']) && ! $this->fileRepo->ownedDiscipline($data['disciplina_id'], $userId)) {
            throw new ValidationException(['disciplina_id' => 'Disciplina inválida.']);
        }

        $this->repo->update([
            'id'   => $id,
            'u'    => $userId,
            'd'    => ! empty($data['disciplina_id']) ? $data['disciplina_id'] : null,
            't'    => trim($data['titulo']),
            'desc' => ! empty($data['descricao']) ? trim($data['descricao']) : null,
            'p'    => $data['prioridade'] ?? 'media',
            's'    => $data['status'] ?? 'a_fazer',
            'dt'   => ! empty($data['data_vencimento']) ? $data['data_vencimento'] : null,
            'r'    => isset($data['recorrente']) ? 1 : 0,
        ]);
    }

    public function delete(string $userId, string $id): void
    {
        $this->find($userId, $id);
        $this->repo->delete($userId, $id);
    }

    public function updateStatus(string $userId, string $id, string $status): void
    {
        $this->find($userId, $id);
        if (! in_array($status, ['a_fazer', 'em_andamento', 'concluido'])) {
            throw new ValidationException(['status' => 'Status inválido.']);
        }
        $this->repo->updateStatus($userId, $id, $status);
    }

    public function addChecklistItem(string $userId, string $taskId, string $description): void
    {
        $this->find($userId, $taskId);
        if (trim($description) === '') {
            throw new ValidationException(['descricao' => 'Descrição não pode ser vazia.']);
        }
        $this->repo->addChecklistItem($taskId, trim($description));
    }

    public function toggleChecklistItem(string $userId, string $taskId, string $itemId, int $concluido): void
    {
        $this->find($userId, $taskId);
        $this->repo->toggleChecklistItem($taskId, $itemId, $concluido);
    }

    public function deleteChecklistItem(string $userId, string $taskId, string $itemId): void
    {
        $this->find($userId, $taskId);
        $this->repo->deleteChecklistItem($taskId, $itemId);
    }

    public function addAttachment(string $userId, string $taskId, string $fileId): void
    {
        $this->find($userId, $taskId);
        $file = $this->fileRepo->file($fileId, $userId);
        if (! $file) {
            throw new ValidationException(['arquivo_id' => 'Arquivo não encontrado ou não pertence a você.']);
        }
        $this->repo->addAttachment($taskId, $fileId);
    }

    public function removeAttachment(string $userId, string $taskId, string $attachmentId): void
    {
        $this->find($userId, $taskId);
        $this->repo->removeAttachment($taskId, $attachmentId);
    }

    private function validate(array $data): void
    {
        $errors = [];
        if (empty($data['titulo']) || trim($data['titulo']) === '') {
            $errors['titulo'] = 'O título é obrigatório.';
        }
        if (! empty($data['prioridade']) && ! in_array($data['prioridade'], ['baixa', 'media', 'alta'])) {
            $errors['prioridade'] = 'Prioridade inválida.';
        }
        if (! empty($data['status']) && ! in_array($data['status'], ['a_fazer', 'em_andamento', 'concluido'])) {
            $errors['status'] = 'Status inválido.';
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
