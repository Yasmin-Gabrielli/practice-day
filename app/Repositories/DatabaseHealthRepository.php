<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\DatabaseException;

final class DatabaseHealthRepository extends Repository
{
    /**
     * Faz uma consulta mínima, somente de leitura, para confirmar a base usada
     * pela conexão atual. Não consulta, cria ou altera tabelas.
     */
    public function currentDatabaseName(): string
    {
        $statement = $this->db->prepare('SELECT DATABASE() AS database_name');
        $statement->execute();
        $result = $statement->fetch();
        $database = $result['database_name'] ?? null;

        if (!is_string($database) || $database === '') {
            throw new DatabaseException('A conexão não retornou o nome do banco de dados ativo.');
        }

        return $database;
    }
}
