<?php

declare(strict_types=1);

namespace App\Validators;

final class RegistrationValidator extends Validator
{
    /** @param array<string, mixed> $input @return array{nome: string, email: string, senha: string} */
    public function validate(array $input): array
    {
        $name = trim((string) ($input['nome'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['senha'] ?? '');
        $confirmation = (string) ($input['confirmacao_senha'] ?? '');

        $this->required(['nome' => $name, 'email' => $email, 'senha' => $password, 'confirmacao_senha' => $confirmation], ['nome', 'email', 'senha', 'confirmacao_senha']);

        if ($name !== '' && mb_strlen($name) > 120) {
            $this->errors['nome'] = 'O nome deve ter no máximo 120 caracteres.';
        }
        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180)) {
            $this->errors['email'] = 'Informe um e-mail válido.';
        }
        if ($password !== '' && (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))) {
            $this->errors['senha'] = 'A senha deve ter ao menos 8 caracteres, uma letra e um número.';
        }
        if ($password !== '' && $confirmation !== '' && !hash_equals($password, $confirmation)) {
            $this->errors['confirmacao_senha'] = 'A confirmação de senha não confere.';
        }

        $this->failWhenInvalid();

        return ['nome' => $name, 'email' => $email, 'senha' => $password];
    }
}
