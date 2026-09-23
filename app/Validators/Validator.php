<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

abstract class Validator
{
    /** @var array<string, string> */
    protected array $errors = [];

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @param array<string, mixed> $data @param list<string> $fields */
    protected function required(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $this->errors[$field] = 'Este campo é obrigatório.';
            }
        }
    }

    protected function failWhenInvalid(): void
    {
        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }
    }
}
