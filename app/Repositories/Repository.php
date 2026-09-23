<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;

abstract class Repository
{
    protected PDO $db;

    /** @param array{host: string, port: string, database: string, username: string, password: string, charset: string} $databaseConfig */
    public function __construct(array $databaseConfig)
    {
        $this->db = Database::connect($databaseConfig);
    }
}
