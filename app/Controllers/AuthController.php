<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Repositories\DashboardWidgetRepository;
use App\Repositories\UserSettingsRepository;
use App\Repositories\UserThemeRepository;
use App\Services\AuthenticationService;
use App\Validators\LoginValidator;
use App\Validators\RegistrationValidator;

final class AuthController extends Controller
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    public function loginForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'title' => 'Entrar | PracticeDay',
            'csrfToken' => Csrf::token(),
            'feedback' => Flash::get('login'),
        ]);
    }

    public function registerForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/register', [
            'title' => 'Criar conta | PracticeDay',
            'csrfToken' => Csrf::token(),
            'feedback' => Flash::get('register'),
        ]);
    }

    public function register(): never
    {
        try {
            Csrf::validate($_POST);
            $data = (new RegistrationValidator())->validate($_POST);
            (new AuthenticationService($this->config))->register($data);
            Flash::set('login', ['success' => 'Conta criada. Entre com seu e-mail e senha.']);
            $this->redirect('/login');
        } catch (ValidationException $exception) {
            Flash::set('register', [
                'errors' => $exception->errors(),
                'old' => ['nome' => trim((string) ($_POST['nome'] ?? '')), 'email' => trim((string) ($_POST['email'] ?? ''))],
            ]);
            $this->redirect('/cadastro');
        }
    }

    public function login(): never
    {
        try {
            Csrf::validate($_POST);
            $data = (new LoginValidator())->validate($_POST);
            $session = (new AuthenticationService($this->config))->login(
                $data['email'],
                $data['senha'],
                $this->clientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            session_regenerate_id(true);
            $_SESSION['user_id'] = $session['id'];
            $_SESSION['user_name'] = $session['nome'];
            $_SESSION['user_avatar'] = $session['avatar'];
            $_SESSION['session_token'] = $session['token'];
            $_SESSION['session_expires_at'] = $session['expires_at'];

            $database = $this->config['database'];
            $userId = $session['id'];
            (new UserThemeRepository($database))->createDefaultPreferences($userId);
            (new DashboardWidgetRepository($database))->ensureDefaultsForUser($userId);
            $_SESSION['user_settings'] = (new UserSettingsRepository($database))->getForUser($userId);

            $this->redirect('/dashboard');
        } catch (ValidationException|AuthenticationException $exception) {
            $errors = $exception instanceof ValidationException ? $exception->errors() : ['credenciais' => $exception->getMessage()];
            Flash::set('login', ['errors' => $errors, 'old' => ['email' => trim((string) ($_POST['email'] ?? ''))]]);
            $this->redirect('/login');
        }
    }

    public function logout(): never
    {
        try {
            Csrf::validate($_POST);
            $userId = $_SESSION['user_id'] ?? null;
            $token = $_SESSION['session_token'] ?? null;

            if (is_string($userId) && is_string($token)) {
                (new AuthenticationService($this->config))->logout($userId, $token);
            }
        } finally {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires' => time() - 3600,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            }
            session_destroy();
        }

        session_start($this->config['session']['options']);
        Flash::set('login', ['success' => 'Sessão encerrada com segurança.']);
        $this->redirect('/login');
    }

    private function clientIp(): string
    {
        return mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 100);
    }
}
