<?php

declare(strict_types=1);

use App\Services\DatabaseHealthService;

require_once dirname(__DIR__) . '/config/bootstrap.php';

$result = (new DatabaseHealthService($config['database']))->check();
echo sprintf("Conexão confirmada com o banco %s.%s", $result['database'], PHP_EOL);
