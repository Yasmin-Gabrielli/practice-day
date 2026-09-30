<?php

declare(strict_types=1);

namespace App\Helpers;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    /** @param callable(...string): void $action */
    public function get(string $path, callable $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    /** @param callable(...string): void $action */
    public function post(string $path, callable $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = ($method === 'HEAD') ? 'GET' : $method;
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

        if ($scriptDirectory !== '' && $scriptDirectory !== '/' && str_starts_with($path, $scriptDirectory)) {
            $path = substr($path, strlen($scriptDirectory)) ?: '/';
        }

        $action = $this->routes[$method][$path] ?? null;
        $parameters = [];

        if ($action === null) {
            foreach ($this->routes[$method] ?? [] as $route => $candidate) {
                $pattern = preg_replace('/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/', '(?P<$1>[^/]+)', preg_quote($route, '#'));
                if ($pattern !== null && preg_match('#^' . $pattern . '$#', $path, $matches) === 1) {
                    $action = $candidate;
                    $parameters = array_values(array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY));
                    break;
                }
            }
        }

        if ($action === null) {
            http_response_code(404);
            echo 'Página não encontrada.';
            return;
        }

        $action(...$parameters);
    }
}
