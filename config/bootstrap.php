<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/env.php';

$config = require __DIR__ . '/app.php';

date_default_timezone_set($config['app']['timezone']);
session_name($config['session']['name']);
session_save_path(dirname(__DIR__) . '/storage/sessions');
session_start($config['session']['options']);
