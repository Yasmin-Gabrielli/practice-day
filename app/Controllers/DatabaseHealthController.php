<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\JsonResponse;
use App\Services\DatabaseHealthService;

/** Controlador de referência para o fluxo Controller → Service → Repository → PDO. */
final class DatabaseHealthController extends Controller
{
    /** @param array{host: string, port: string, database: string, username: string, password: string, charset: string} $databaseConfig */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function show(): never
    {
        $status = (new DatabaseHealthService($this->databaseConfig))->check();
        JsonResponse::send(['data' => $status]);
    }
}
