<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\NotificationService;

final class NotificationController extends Controller
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    private function auth(): string
    {
        try {
            (new AuthenticationMiddleware($this->config))->handle();
        } catch (AuthenticationException) {
            $this->redirect('/login');
        }

        return (string) ($_SESSION['user_id'] ?? '');
    }

    public function index(): void
    {
        $userId = $this->auth();
        $feed = (new NotificationService($this->config))->feedForUser($userId);
        $_SESSION['notification_pending_count'] = $feed['pending_count'];

        $this->view('notificacoes/index', [
            'title' => 'Notificações | PracticeDay',
            'activePage' => 'notificacoes',
            'csrfToken' => Csrf::token(),
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'groups' => $feed['groups'],
            'pendingCount' => $feed['pending_count'],
            'feedback' => Flash::get('notificacoes'),
        ]);
    }

    /**
     * Feed em JSON usado pelo modal de notificações do sino (topo).
     */
    public function feed(): never
    {
        $userId = $this->auth();

        try {
            $feed = (new NotificationService($this->config))->feedForUser($userId);
            $_SESSION['notification_pending_count'] = $feed['pending_count'];

            $this->jsonOk([
                'pending_count' => $feed['pending_count'],
                'groups' => $feed['groups'],
            ]);
        } catch (\Throwable $exception) {
            $this->jsonFail(['geral' => $exception->getMessage()]);
        }
    }

    public function markViewed(): never
    {
        $userId = $this->auth();
        $ajax = $this->expectsJson();

        try {
            Csrf::validate($_POST);
            $key = trim((string) ($_POST['notification_key'] ?? ''));
            if ($key === '') {
                throw new \InvalidArgumentException('Notificação inválida.');
            }

            (new NotificationService($this->config))->markAsViewed($userId, $key);
            $_SESSION['notification_pending_count'] = (new NotificationService($this->config))->pendingCount($userId);

            if ($ajax) {
                $this->jsonOk(['pending_count' => (int) $_SESSION['notification_pending_count']]);
            }

            Flash::set('notificacoes', ['success' => 'Notificação marcada como visualizada.']);
        } catch (\Throwable $exception) {
            if ($ajax) {
                $this->jsonFail(['geral' => $exception->getMessage()]);
            }

            Flash::set('notificacoes', ['errors' => ['geral' => $exception->getMessage()]]);
        }

        $redirect = trim((string) ($_POST['redirect'] ?? '/notificacoes'));
        if ($redirect === '' || !str_starts_with($redirect, '/')) {
            $redirect = '/notificacoes';
        }

        $this->redirect($redirect);
    }

    public function markAllViewed(): never
    {
        $userId = $this->auth();
        $ajax = $this->expectsJson();

        try {
            Csrf::validate($_POST);
            (new NotificationService($this->config))->markAllAsViewed($userId);
            $_SESSION['notification_pending_count'] = 0;

            if ($ajax) {
                $this->jsonOk(['pending_count' => 0]);
            }

            Flash::set('notificacoes', ['success' => 'Todas as notificações foram marcadas como visualizadas.']);
        } catch (\Throwable $exception) {
            if ($ajax) {
                $this->jsonFail(['geral' => $exception->getMessage()]);
            }

            Flash::set('notificacoes', ['errors' => ['geral' => $exception->getMessage()]]);
        }

        $this->redirect('/notificacoes');
    }

    private function expectsJson(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    /** @param array<string, mixed> $extra */
    private function jsonOk(array $extra = []): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true] + $extra);
        exit;
    }

    /** @param array<string, string> $errors */
    private function jsonFail(array $errors): never
    {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
}
