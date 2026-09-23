<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DatabaseException;
use App\Repositories\DatabaseHealthRepository;

final class DatabaseHealthService extends Service
{
    private DatabaseHealthRepository $repository;

    /** @param array{host: string, port: string, database: string, username: string, password: string, charset: string} $databaseConfig */
    public function __construct(array $databaseConfig)
    {
        $this->repository = new DatabaseHealthRepository($databaseConfig);
    }

    /** @return array{database: string, connected: true} */
    public function check(): array
    {
        $database = $this->repository->currentDatabaseName();

        if ($database !== 'practice_day') {
            throw new DatabaseException('A conexão foi estabelecida com um banco diferente do esperado.');
        }

        return ['database' => $database, 'connected' => true];
    }
}
