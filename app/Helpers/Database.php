<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use App\Exceptions\DatabaseException;

final class Database
{
    private static ?PDO $connection = null;

    /** @param array{host: string, port: string, database: string, username: string, password: string, charset: string} $config */
    public static function connect(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new DatabaseException('Não foi possível conectar ao banco de dados configurado.', 0, $exception);
        }

        return self::$connection;
    }
}
