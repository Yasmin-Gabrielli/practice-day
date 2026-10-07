<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\Lang;
use App\Middleware\AuthenticationMiddleware;
use App\Repositories\DashboardWidgetRepository;
use App\Services\SettingsService;

final class SettingsController extends Controller
{
    public function __construct(private readonly array $config)
    {
    }

    private function auth(): void
    {
        try {
            (new AuthenticationMiddleware($this->config))->handle();
        } catch (AuthenticationException) {
            $this->redirect('/login');
        }
    }

    private function u(): string
    {
        return (string) ($_SESSION['user_id'] ?? '');
    }

    public function index(): void
    {
        $this->auth();
        $userId = $this->u();
        $service = new SettingsService($this->config);

        try {
            $page = $service->pageData($userId);
        } catch (ValidationException $exception) {
            Flash::set('configuracoes', ['errors' => $exception->errors()]);
            $this->redirect('/login');
        }

        $this->view('configuracoes/index', [
            'activePage' => 'configuracoes',
            'title' => Lang::get('settings.title') . ' - PracticeDay',
            'userName' => $_SESSION['user_name'] ?? '',
            'userAvatar' => $_SESSION['user_avatar'] ?? null,
            'csrfToken' => Csrf::token(),
            'user' => $page['user'],
            'settings' => $page['settings'],
            'notifications' => $page['notifications'],
            'widgets' => $page['widgets'],
            'widgetLabels' => DashboardWidgetRepository::typeLabels(),
            'feedback' => Flash::get('configuracoes'),
        ]);
    }

    public function update(): never
    {
        $this->auth();
        try {
            if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                Flash::set('configuracoes', ['errors' => ['geral' => Lang::get('settings.post_max_error')]]);
                $this->redirect('/configuracoes');
            }

            Csrf::validate($_POST);
            $userId = $this->u();
            $result = (new SettingsService($this->config))->updateForUser($userId, $_POST, $_FILES);

            $_SESSION['user_name'] = $result['user_name'];
            $_SESSION['user_avatar'] = $result['user_avatar'];
            $_SESSION['user_settings'] = $result['user_settings'];

            Flash::set('configuracoes', ['success' => Lang::get('settings.saved_flash')]);
        } catch (ValidationException $exception) {
            Flash::set('configuracoes', ['errors' => $exception->errors()]);
        } catch (\Throwable $exception) {
            Flash::set('configuracoes', ['errors' => ['geral' => $exception->getMessage()]]);
        }

        $this->redirect('/configuracoes');
    }

    public function serveAvatar(): never
    {
        $this->serveUserImage('avatar');
    }

    public function serveWallpaper(): never
    {
        $this->serveUserImage('wallpaper');
    }

    private function serveUserImage(string $kind): never
    {
        $userId = $this->u();
        if ($userId === '' || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $userId)) {
            http_response_code(404);
            exit;
        }

        $files = glob(dirname(__DIR__, 2) . '/storage/users/' . $userId . '/' . $kind . '.*') ?: [];
        $files = array_values(array_filter(
            $files,
            static fn (string $file): bool => in_array(
                strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                ['jpg', 'jpeg', 'png', 'webp'],
                true
            )
        ));

        if ($files === []) {
            http_response_code(404);
            exit;
        }

        $path = $files[0];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=86400');
        readfile($path);
        exit;
    }
}
