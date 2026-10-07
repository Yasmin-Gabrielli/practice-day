<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DisciplineController;
use App\Controllers\FavoritesController;
use App\Controllers\FileController;
use App\Controllers\CalendarController;
use App\Controllers\FileViewController;
use App\Controllers\PlannerController;
use App\Controllers\NoteController;
use App\Controllers\LibraryController;
use App\Controllers\ScannerController;
use App\Controllers\SettingsController;
use App\Controllers\TrashController;
use App\Controllers\NotificationController;
use App\Controllers\PomodoroController;
use App\Controllers\StatisticsController;
use App\Helpers\ErrorHandler;
use App\Helpers\Router;

require_once dirname(__DIR__) . '/config/bootstrap.php';

ErrorHandler::register($config);

$router = new Router();
$authController = new AuthController($config);
$dashboardController = new DashboardController($config);
$disciplineController = new DisciplineController($config);
$favoritesController = new FavoritesController($config);
$fileController = new FileController($config);
$fileViewController = new FileViewController($config);
$plannerController = new PlannerController($config);
$trashController = new TrashController($config);
$calendarController = new CalendarController($config);
$noteController = new NoteController($config);
$libraryController = new LibraryController($config);
$scannerController = new ScannerController($config);
$settingsController = new SettingsController($config);
$notificationController = new NotificationController($config);
$pomodoroController = new PomodoroController($config);
$statisticsController = new StatisticsController($config);

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
$router->get('/arquivos/visualizar/{id}/epub', static fn (string $id) => $fileViewController->viewEpub($id));
$router->get('/arquivos/visualizar/{id}', static fn (string $id) => $fileViewController->show($id));
$router->get('/arquivos/{id}/servir', static fn (string $id) => $fileViewController->serve($id));
$router->post('/arquivos/biblioteca/{libraryId}/progresso', static fn (string $libraryId) => $fileViewController->saveProgress($libraryId));
$router->post('/arquivos/biblioteca/{libraryId}/marcadores', static fn (string $libraryId) => $fileViewController->saveBookmark($libraryId));
$router->post('/arquivos/biblioteca/{libraryId}/destaques', static fn (string $libraryId) => $fileViewController->saveHighlight($libraryId));
$router->post('/arquivos/biblioteca/{libraryId}/anotacoes', static fn (string $libraryId) => $fileViewController->saveNote($libraryId));
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
$router->get('/favoritos', static fn () => $favoritesController->index());
$router->post('/favoritos/{id}/remover', static fn (string $id) => $favoritesController->remove($id));
$router->get('/lixeira', static fn () => $trashController->index());
$router->post('/lixeira/{id}/restaurar', static fn (string $id) => $trashController->restore($id));
$router->post('/lixeira/{id}/destruir', static fn (string $id) => $trashController->destroy($id));
$router->post('/lixeira/esvaziar', static fn () => $trashController->empty());

$router->get('/planner', static fn () => $plannerController->index());
$router->post('/planner', static fn () => $plannerController->create());
$router->post('/planner/{id}/editar', static fn (string $id) => $plannerController->update($id));
$router->post('/planner/{id}/excluir', static fn (string $id) => $plannerController->delete($id));
$router->post('/planner/{id}/status', static fn (string $id) => $plannerController->updateStatus($id));
$router->get('/planner/{id}/view', static fn (string $id) => $plannerController->viewTask($id));
$router->post('/planner/{id}/checklist', static fn (string $id) => $plannerController->addChecklist($id));
$router->post('/planner/{id}/checklist/{itemId}/toggle', static fn (string $id, string $itemId) => $plannerController->toggleChecklist($id, $itemId));
$router->post('/planner/{id}/checklist/{itemId}/excluir', static fn (string $id, string $itemId) => $plannerController->deleteChecklist($id, $itemId));
$router->post('/planner/{id}/anexar', static fn (string $id) => $plannerController->attachFile($id));
$router->post('/planner/{id}/anexos/{attachmentId}/excluir', static fn (string $id, string $attachmentId) => $plannerController->detachFile($id, $attachmentId));

$router->get('/calendario', static fn () => $calendarController->index());
$router->get('/calendario/events', static fn () => $calendarController->fetchEvents());
$router->post('/calendario', static fn () => $calendarController->create());
$router->post('/calendario/{id}/editar', static fn (string $id) => $calendarController->update($id));
$router->post('/calendario/{id}/excluir', static fn (string $id) => $calendarController->delete($id));
$router->get('/calendario/{id}/view', static fn (string $id) => $calendarController->viewEvent($id));
$router->get('/calendario/tarefa/{id}/view', static fn (string $id) => $calendarController->viewTask($id));

$router->get('/notas', static fn () => $noteController->index());
$router->get('/notas/nova', static fn () => $noteController->newForm());
$router->post('/notas', static fn () => $noteController->create());
$router->get('/notas/{id}/editar', static fn (string $id) => $noteController->editForm($id));
$router->post('/notas/{id}/editar', static fn (string $id) => $noteController->update($id));
$router->post('/notas/{id}/excluir', static fn (string $id) => $noteController->delete($id));
$router->get('/biblioteca', static fn () => $libraryController->index());
$router->post('/biblioteca/{id}/adicionar', static fn (string $id) => $libraryController->add($id));
$router->get('/scanner', static fn () => $scannerController->index());
    $router->post('/scanner', static fn () => $scannerController->create());
    $router->post('/scanner/{id}/excluir', static fn (string $id) => $scannerController->delete($id));
$router->get('/scanner/paginas/{id}/imagem', static fn (string $id) => $scannerController->image($id));

$router->get('/pomodoro', static fn () => $pomodoroController->index());
$router->post('/pomodoro', static fn () => $pomodoroController->save());

$router->get('/estatisticas', static fn () => $statisticsController->index());

$router->get('/notificacoes', static fn () => $notificationController->index());
$router->post('/notificacoes/visualizar', static fn () => $notificationController->markViewed());
$router->post('/notificacoes/visualizar-todas', static fn () => $notificationController->markAllViewed());

$router->get('/configuracoes', static fn () => $settingsController->index());
$router->post('/configuracoes', static fn () => $settingsController->update());
$router->get('/perfil/avatar', static fn () => $settingsController->serveAvatar());
$router->get('/perfil/wallpaper', static fn () => $settingsController->serveWallpaper());

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    ErrorHandler::handleException($exception, $config);
}
