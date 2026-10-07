<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Helpers\Lang;
use App\Helpers\Url;
use App\Repositories\DashboardWidgetRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserSettingsRepository;
use App\Repositories\UserThemeRepository;

final class SettingsService extends Service
{
    private const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    /** @var array<string, list<string>> */
    private const IMAGE_MIME_BY_EXT = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    private UserRepository $users;
    private UserSettingsRepository $settings;
    private UserThemeRepository $themes;
    private DashboardWidgetRepository $widgets;

    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}} $config */
    public function __construct(private readonly array $config)
    {
        $database = $config['database'];
        $this->users = new UserRepository($database);
        $this->settings = new UserSettingsRepository($database);
        $this->themes = new UserThemeRepository($database);
        $this->widgets = new DashboardWidgetRepository($database);
    }

    /** @return array{user: array<string, mixed>, settings: array<string, mixed>, notifications: array<string, bool>, widgets: list<array<string, mixed>>} */
    public function pageData(string $userId): array
    {
        $this->settings->ensureForUser($userId);
        $this->themes->createDefaultPreferences($userId);
        $this->widgets->ensureDefaultsForUser($userId);

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ValidationException(['geral' => Lang::get('settings.error.user_not_found')]);
        }

        return [
            'user' => $user,
            'settings' => $this->settings->getForUser($userId),
            'notifications' => $this->themes->getNotificationPreferences($userId),
            'widgets' => $this->widgets->forUser($userId),
        ];
    }

    /** @param array<string, mixed> $input @param array<string, mixed> $files */
    public function updateForUser(string $userId, array $input, array $files = []): array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ValidationException(['geral' => Lang::get('settings.error.user_not_found')]);
        }

        $name = trim((string) ($input['nome'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($name === '' || $email === '') {
            throw new ValidationException(['geral' => Lang::get('settings.error.name_email_required')]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.invalid_email')]);
        }

        $existing = $this->users->findByEmail($email);
        if ($existing !== null && (string) $existing['id'] !== $userId) {
            throw new ValidationException(['geral' => Lang::get('settings.error.email_taken')]);
        }

        $primary = (string) ($input['cor_primaria'] ?? '#2563eb');
        $secondary = (string) ($input['cor_secundaria'] ?? '#ffffff');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary) || !preg_match('/^#[0-9a-fA-F]{6}$/', $secondary)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.invalid_hex')]);
        }

        $fontSize = (string) ($input['tamanho_fonte'] ?? 'médio');
        if (!in_array($fontSize, ['pequeno', 'médio', 'grande'], true)) {
            $fontSize = 'médio';
        }

        $language = (string) ($input['idioma'] ?? 'pt-BR');
        if (!in_array($language, ['pt-BR', 'en-US'], true)) {
            $language = 'pt-BR';
        }

        $themeMode = (string) ($input['tema_modo'] ?? 'claro');

        $avatar = $this->resolveImage(
            $userId,
            $input,
            $files,
            'avatar_arquivo',
            'remover_avatar',
            'avatar',
            '/perfil/avatar',
            trim((string) ($user['avatar'] ?? ''))
        );

        $wallpaper = $this->resolveImage(
            $userId,
            $input,
            $files,
            'papel_parede_arquivo',
            'remover_papel_parede',
            'wallpaper',
            '/perfil/wallpaper',
            trim((string) ($this->settings->getForUser($userId)['papel_parede'] ?? ''))
        );

        $this->users->updateProfile($userId, $name, $email, $avatar);

        $this->settings->ensureForUser($userId);
        $this->settings->updateForUser($userId, [
            'modo_escuro' => $themeMode === 'escuro' ? 1 : 0,
            'cor_primaria' => $primary,
            'cor_secundaria' => $secondary,
            'papel_parede' => $wallpaper,
            'tamanho_fonte' => $fontSize,
            'animacoes_ativas' => !empty($input['animacoes_ativas']) ? 1 : 0,
            'idioma' => $language,
        ]);

        $this->themes->saveNotificationPreferences($userId, [
            'notificacoes_lembretes_tarefas' => !empty($input['notificacoes_lembretes_tarefas']),
            'notificacoes_eventos_calendario' => !empty($input['notificacoes_eventos_calendario']),
        ]);

        $visibleWidgets = is_array($input['widget_visivel'] ?? null) ? $input['widget_visivel'] : [];
        $this->widgets->syncVisibilityForUser($userId, $visibleWidgets);

        return [
            'user_name' => $name,
            'user_avatar' => $avatar,
            'user_settings' => $this->settings->getForUser($userId),
        ];
    }

    /**
     * Aplica o upload de uma imagem de perfil (avatar/wallpaper).
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $files
     */
    private function resolveImage(
        string $userId,
        array $input,
        array $files,
        string $fileKey,
        string $removeKey,
        string $kind,
        string $publicPath,
        string $current
    ): ?string {
        if (!empty($input[$removeKey])) {
            $this->deleteImageFiles($userId, $kind);

            return null;
        }

        $upload = $files[$fileKey] ?? null;
        if (is_array($upload) && (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $this->storeImage($userId, $upload, $kind);

            return Url::to($publicPath);
        }

        return $current !== '' ? $current : null;
    }

    /** @param array<string, mixed> $upload */
    private function storeImage(string $userId, array $upload, string $kind): void
    {
        $error = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new ValidationException(['geral' => self::uploadErrorMessage($error)]);
        }

        $tmp = (string) ($upload['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.file_process')]);
        }

        $size = (int) ($upload['size'] ?? 0);
        if ($size <= 0 || $size > self::IMAGE_MAX_BYTES) {
            throw new ValidationException(['geral' => Lang::get('settings.error.file_size')]);
        }

        $original = basename(str_replace(["\r", "\n", "\t"], '', (string) ($upload['name'] ?? '')));
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!isset(self::IMAGE_MIME_BY_EXT[$ext])) {
            throw new ValidationException(['geral' => Lang::get('settings.error.file_type')]);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
        if (!in_array($mime, self::IMAGE_MIME_BY_EXT[$ext], true)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.file_mismatch')]);
        }

        if (@getimagesize($tmp) === false) {
            throw new ValidationException(['geral' => Lang::get('settings.error.file_invalid')]);
        }

        $dir = $this->userImagesDir($userId);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.dir')]);
        }

        $this->deleteImageFiles($userId, $kind);

        $destination = $dir . '/' . $kind . '.' . $ext;
        if (!move_uploaded_file($tmp, $destination)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.save')]);
        }

        @chmod($destination, 0644);
    }

    private function deleteImageFiles(string $userId, string $kind): void
    {
        $dir = $this->userImagesDir($userId);
        foreach (glob($dir . '/' . $kind . '.*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    private function userImagesDir(string $userId): string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $userId)) {
            throw new ValidationException(['geral' => Lang::get('settings.error.user_id')]);
        }

        return dirname(__DIR__, 2) . '/storage/users/' . $userId;
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => Lang::get('settings.error.upload_too_big'),
            UPLOAD_ERR_PARTIAL => Lang::get('settings.error.upload_partial'),
            UPLOAD_ERR_NO_FILE => Lang::get('settings.error.upload_no_file'),
            default => Lang::get('settings.error.upload_failed'),
        };
    }
}
