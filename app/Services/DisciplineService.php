<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Helpers\Uuid;
use App\Repositories\DisciplineRepository;

final class DisciplineService extends Service
{
    private DisciplineRepository $disciplines;

    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}} $config */
    public function __construct(array $config)
    {
        $this->disciplines = new DisciplineRepository($config['database']);
    }

    /** @return list<array<string, mixed>> */
    public function list(string $userId): array { return $this->disciplines->allForUser($userId); }

    /** @param array{nome: string, cor: string, icone: string} $data */
    public function create(string $userId, array $data): void
    {
        $this->disciplines->create(Uuid::v4(), $userId, $data['nome'], $data['cor'], $data['icone']);
    }

    /** @return array<string, mixed> */
    public function detail(string $id, string $userId): array
    {
        $discipline = $this->disciplineOrFail($id, $userId);
        return ['discipline' => $discipline, 'files' => $this->disciplines->files($id, $userId), 'tasks' => $this->disciplines->tasks($id, $userId), 'notes' => $this->disciplines->notes($id, $userId)];
    }

    /** @return array<string, mixed> */
    public function find(string $id, string $userId): array { return $this->disciplineOrFail($id, $userId); }

    /** @param array{nome: string, cor: string, icone: string} $data */
    public function update(string $id, string $userId, array $data): void
    {
        $this->disciplineOrFail($id, $userId);
        $this->disciplines->update($id, $userId, $data['nome'], $data['cor'], $data['icone']);
    }

    /** @return array<string, mixed> */
    public function deletionImpact(string $id, string $userId): array { return $this->disciplineOrFail($id, $userId); }

    public function delete(string $id, string $userId): void
    {
        $this->disciplineOrFail($id, $userId);
        $this->disciplines->delete($id, $userId);
    }

    /** @return array<string, mixed> */
    private function disciplineOrFail(string $id, string $userId): array
    {
        $discipline = $this->disciplines->findForUser($id, $userId);
        if ($discipline === null) throw new NotFoundException('Disciplina não encontrada.');
        return $discipline;
    }
}
