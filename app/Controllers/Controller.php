<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__, 2) . '/views/' . $view . '.php';
    }

    protected function redirect(string $path): never
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        header('Location: ' . ($basePath === '' ? '' : $basePath) . $path, true, 302);
        exit;
    }
}
