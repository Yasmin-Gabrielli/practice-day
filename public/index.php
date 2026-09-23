<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DisciplineController;
use App\Helpers\ErrorHandler;
use App\Helpers\Router;

require_once dirname(__DIR__) . '/config/bootstrap.php';

ErrorHandler::register($config);

$router = new Router();
$authController = new AuthController($config);
$dashboardController = new DashboardController($config);
$disciplineController = new DisciplineController($config);

$router->get('/', static fn () => $dashboardController->index());
$router->get('/dashboard', static fn () => $dashboardController->index());
$router->get('/login', static fn () => $authController->loginForm());
$router->post('/login', static fn () => $authController->login());
$router->get('/cadastro', static fn () => $authController->registerForm());
$router->post('/cadastro', static fn () => $authController->register());
$router->post('/logout', static fn () => $authController->logout());
$router->get('/disciplinas', static fn () => $disciplineController->index());
$router->post('/disciplinas', static fn () => $disciplineController->create());
$router->get('/disciplinas/{id}', static fn (string $id) => $disciplineController->show($id));
$router->get('/disciplinas/{id}/editar', static fn (string $id) => $disciplineController->editForm($id));
$router->post('/disciplinas/{id}/editar', static fn (string $id) => $disciplineController->update($id));
$router->post('/disciplinas/{id}/excluir', static fn (string $id) => $disciplineController->delete($id));

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    ErrorHandler::handleException($exception, $config);
}
