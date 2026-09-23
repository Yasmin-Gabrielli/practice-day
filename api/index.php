<?php

declare(strict_types=1);

use App\Helpers\ErrorHandler;
use App\Helpers\JsonResponse;

require_once dirname(__DIR__) . '/config/bootstrap.php';

ErrorHandler::register($config);

JsonResponse::send(['message' => 'Endpoint de API não encontrado.'], 404);
