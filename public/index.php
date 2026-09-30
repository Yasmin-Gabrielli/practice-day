<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DisciplineController;
use App\Controllers\FileController;
use App\Helpers\ErrorHandler;
use App\Helpers\Router;

require_once dirname(__DIR__) . '/config/bootstrap.php';

ErrorHandler::register($config);

$router = new Router();
$authController = new AuthController($config);
$dashboardController = new DashboardController($config);
$disciplineController = new DisciplineController($config);
$fileController = new FileController($config);

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
$router->get('/arquivos', static fn () => $fileController->index());
$router->post('/arquivos/upload', static fn () => $fileController->upload());
$router->post('/arquivos/pastas', static fn () => $fileController->createFolder());
$router->post('/arquivos/pastas/{id}/renomear', static fn (string $id) => $fileController->renameFolder($id));
$router->post('/arquivos/pastas/{id}/mover', static fn (string $id) => $fileController->moveFolder($id));
$router->post('/arquivos/pastas/{id}/excluir', static fn (string $id) => $fileController->deleteFolder($id));
$router->post('/arquivos/{id}/renomear', static fn (string $id) => $fileController->rename($id));
$router->post('/arquivos/{id}/favorito', static fn (string $id) => $fileController->favorite($id));
$router->get('/arquivos/{id}/download', static fn (string $id) => $fileController->download($id));
$router->post('/arquivos/{id}/mover', static fn (string $id) => $fileController->moveFile($id));
$router->post('/arquivos/{id}/tags', static fn (string $id) => $fileController->updateTags($id));
$router->post('/arquivos/{id}/excluir', static fn (string $id) => $fileController->moveToTrash($id));
$router->post('/arquivos/{id}/restaurar', static fn (string $id) => $fileController->restoreFromTrash($id));
$router->post('/arquivos/{id}/destruir', static fn (string $id) => $fileController->deletePermanently($id));
$router->get('/arquivos/{id}/historico', static fn (string $id) => $fileController->history($id));
$router->post('/arquivos/tags', static fn () => $fileController->createTag());

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    ErrorHandler::handleException($exception, $config);
}
