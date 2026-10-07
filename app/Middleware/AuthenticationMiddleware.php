<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\AuthenticationException;
use App\Repositories\UserSettingsRepository;
use App\Services\AuthenticationService;
use App\Services\NotificationService;

/** Ponto de extensão para proteger rotas quando o login for implementado. */
final class AuthenticationMiddleware implements Middleware
{
    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}, auth: array{session_lifetime_minutes: int}} $config */
    public function __construct(private readonly array $config)
    {
    }

    public function handle(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $token  = $_SESSION['session_token'] ?? null;

        if (!is_string($userId) || !is_string($token) || !(new AuthenticationService($this->config))->isSessionValid($userId, $token)) {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_avatar'], $_SESSION['session_token'], $_SESSION['session_expires_at'], $_SESSION['user_settings']);
            throw new AuthenticationException('Autenticação necessária.');
        }

        if (!isset($_SESSION['user_settings'])) {
            $settingsRepository      = new UserSettingsRepository($this->config['database']);
            $_SESSION['user_settings'] = $settingsRepository->getForUser($userId);
        }

        // Atualiza a contagem de notificações pendentes na sessão a cada requisição
        // para manter o badge da navbar sempre preciso.
        if (!isset($_SESSION['_notif_refreshed_at']) || (time() - (int) $_SESSION['_notif_refreshed_at']) > 60) {
            try {
                $_SESSION['notification_pending_count'] = (new NotificationService($this->config))->pendingCount($userId);
            } catch (\Throwable) {
                $_SESSION['notification_pending_count'] = $_SESSION['notification_pending_count'] ?? 0;
            }
            $_SESSION['_notif_refreshed_at'] = time();
        }
    }
}
