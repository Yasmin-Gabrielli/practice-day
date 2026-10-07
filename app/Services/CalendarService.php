<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Uuid;
use App\Repositories\CalendarRepository;
use App\Repositories\DisciplineRepository;

final class CalendarService extends Service
{
    private CalendarRepository $repo;
    private DisciplineRepository $disciplineRepo;

    public function __construct(private readonly array $config)
    {
        $this->repo = new CalendarRepository($config['database']);
        $this->disciplineRepo = new DisciplineRepository($config['database']);
    }

    public function getEvents(string $userId, string $start, string $end): array
    {
        return $this->repo->getEvents($userId, $start, $end);
    }

    public function getTasksAsEvents(string $userId, string $start, string $end): array
    {
        return $this->repo->getTasksAsEvents($userId, $start, $end);
    }

    public function getUpcomingEvents(string $userId, int $limit = 10): array
    {
        return $this->repo->getUpcomingEvents($userId, $limit);
    }

    public function findEvent(string $userId, string $id): array
    {
        $event = $this->repo->findEvent($userId, $id);
        if (!$event) {
            throw new NotFoundException('Evento não encontrado.');
        }
        return $event;
    }

    public function findTaskAsEvent(string $userId, string $id): array
    {
        $task = $this->repo->findTaskAsEvent($userId, $id);
        if (!$task) {
            throw new NotFoundException('Tarefa não encontrada.');
        }
        return $task;
    }

    public function getDisciplines(string $userId): array
    {
        return $this->disciplineRepo->allForUser($userId);
    }

    public function createEvent(string $userId, array $data): void
    {
        $this->validateEvent($data);
        $this->validateDiscipline($userId, $data['disciplina_id'] ?? null);
        $id = Uuid::v4();
        $this->repo->createEvent([
            'id' => $id,
            'u' => $userId,
            'd' => !empty($data['disciplina_id']) ? $data['disciplina_id'] : null,
            't' => trim($data['titulo']),
            'desc' => !empty($data['descricao']) ? trim($data['descricao']) : null,
            'di' => $data['data_inicio'],
            'df' => $data['data_fim'],
            'tp' => $data['tipo'] ?? 'evento'
        ]);

        if (!empty($data['lembretes']) && is_array($data['lembretes'])) {
            foreach ($data['lembretes'] as $lembrete) {
                if (!empty($lembrete)) {
                    $this->repo->addReminder(Uuid::v4(), $id, $lembrete);
                }
            }
        }
    }

    public function updateEvent(string $userId, string $id, array $data): void
    {
        $this->findEvent($userId, $id);
        $this->validateEvent($data);
        $this->validateDiscipline($userId, $data['disciplina_id'] ?? null);
        $this->repo->updateEvent([
            'id' => $id,
            'u' => $userId,
            'd' => !empty($data['disciplina_id']) ? $data['disciplina_id'] : null,
            't' => trim($data['titulo']),
            'desc' => !empty($data['descricao']) ? trim($data['descricao']) : null,
            'di' => $data['data_inicio'],
            'df' => $data['data_fim'],
            'tp' => $data['tipo'] ?? 'evento'
        ]);

        $this->repo->deleteRemindersByEvent($id);
        if (!empty($data['lembretes']) && is_array($data['lembretes'])) {
            foreach ($data['lembretes'] as $lembrete) {
                if (!empty($lembrete)) {
                    $this->repo->addReminder(Uuid::v4(), $id, $lembrete);
                }
            }
        }
    }

    public function deleteEvent(string $userId, string $id): void
    {
        $this->findEvent($userId, $id);
        $this->repo->deleteRemindersByEvent($id);
        $this->repo->deleteEvent($userId, $id);
    }

    private function validateEvent(array $data): void
    {
        $errors = [];
        if (empty($data['titulo']) || trim($data['titulo']) === '') {
            $errors['titulo'] = 'O título é obrigatório.';
        }
        if (empty($data['data_inicio'])) {
            $errors['data_inicio'] = 'A data de início é obrigatória.';
        }
        if (empty($data['data_fim'])) {
            $errors['data_fim'] = 'A data de término é obrigatória.';
        }
        if (!empty($data['data_inicio']) && !empty($data['data_fim']) && $data['data_inicio'] > $data['data_fim']) {
            $errors['datas'] = 'A data de término deve ser posterior à data de início.';
        }
        if (!empty($data['tipo']) && !in_array($data['tipo'], ['prova', 'trabalho', 'evento', 'lembrete'])) {
            $errors['tipo'] = 'Tipo de evento inválido.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function validateDiscipline(string $userId, mixed $disciplineId): void
    {
        if ($disciplineId === null || $disciplineId === '') {
            return;
        }

        if ($this->disciplineRepo->findForUser((string) $disciplineId, $userId) === null) {
            throw new ValidationException(['disciplina_id' => 'Disciplina inválida.']);
        }
    }
}
