<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Helpers\Database;
use App\Helpers\Uuid;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserSessionRepository;
use App\Repositories\UserSettingsRepository;
use DateTimeImmutable;
use PDOException;

final class AuthenticationService extends Service
{
    private UserRepository $users;
    private UserSettingsRepository $settings;
    private UserSessionRepository $sessions;
    private LoginAttemptRepository $attempts;

    /** @param array{database: array{host: string, port: string, database: string, username: string, password: string, charset: string}, auth: array{session_lifetime_minutes: int}} $config */
    public function __construct(private readonly array $config)
    {
        $databaseConfig = $config['database'];
        $this->users = new UserRepository($databaseConfig);
        $this->settings = new UserSettingsRepository($databaseConfig);
        $this->sessions = new UserSessionRepository($databaseConfig);
        $this->attempts = new LoginAttemptRepository($databaseConfig);
    }

    /** @param array{nome: string, email: string, senha: string} $input */
    public function register(array $input): void
    {
        if ($this->users->existsByEmail($input['email'])) {
            throw new ValidationException(['email' => 'Já existe uma conta para este e-mail.']);
        }

        $userId = Uuid::v4();
        $connection = Database::connect($this->config['database']);

        try {
            $connection->beginTransaction();
            $this->users->create($userId, $input['nome'], $input['email'], password_hash($input['senha'], PASSWORD_DEFAULT));
            $this->settings->createDefaults(Uuid::v4(), $userId);
            $connection->commit();
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            if ($exception->getCode() === '23000') {
                throw new ValidationException(['email' => 'Já existe uma conta para este e-mail.']);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    /** @return array{id: string, nome: string, avatar: string|null, token: string, expires_at: string} */
    public function login(string $email, string $password, string $ipAddress, string $userAgent): array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, (string) $user['senha_hash'])) {
            $this->attempts->record(Uuid::v4(), $email, $ipAddress, false);
            throw new AuthenticationException('E-mail ou senha inválidos.');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . $this->config['auth']['session_lifetime_minutes'] . ' minutes')
            ->format('Y-m-d H:i:s');

        $this->sessions->create(
            Uuid::v4(),
            (string) $user['id'],
            hash('sha256', $token),
            mb_substr($ipAddress, 0, 100),
            mb_substr($userAgent, 0, 65535),
            $expiresAt
        );
        $this->attempts->record(Uuid::v4(), $email, $ipAddress, true);

        return [
            'id' => (string) $user['id'],
            'nome' => (string) $user['nome'],
            'avatar' => is_string($user['avatar']) ? $user['avatar'] : null,
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function logout(string $userId, string $token): void
    {
        $this->sessions->invalidate($userId, hash('sha256', $token));
    }

    public function isSessionValid(string $userId, string $token): bool
    {
        return $this->sessions->isValid($userId, hash('sha256', $token));
    }
}
