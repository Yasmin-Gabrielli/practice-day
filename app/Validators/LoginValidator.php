<?php

declare(strict_types=1);

namespace App\Validators;

final class LoginValidator extends Validator
{
    /** @param array<string, mixed> $input @return array{email: string, senha: string} */
    public function validate(array $input): array
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['senha'] ?? '');
        $this->required(['email' => $email, 'senha' => $password], ['email', 'senha']);

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Informe um e-mail válido.';
        }

        $this->failWhenInvalid();

        return ['email' => $email, 'senha' => $password];
    }
}
