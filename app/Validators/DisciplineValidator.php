<?php

declare(strict_types=1);

namespace App\Validators;

final class DisciplineValidator extends Validator
{
    private const ICONS = ['bi-book', 'bi-calculator', 'bi-flask', 'bi-code-slash', 'bi-globe-americas', 'bi-translate', 'bi-palette', 'bi-music-note-beamed'];

    /** @param array<string, mixed> $input @return array{nome: string, cor: string, icone: string} */
    public function validate(array $input): array
    {
        $name = trim((string) ($input['nome'] ?? ''));
        $color = trim((string) ($input['cor'] ?? ''));
        $icon = trim((string) ($input['icone'] ?? ''));
        $this->required(['nome' => $name, 'cor' => $color, 'icone' => $icon], ['nome', 'cor', 'icone']);
        if ($name !== '' && mb_strlen($name) > 100) $this->errors['nome'] = 'O nome deve ter no máximo 100 caracteres.';
        if ($color !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $this->errors['cor'] = 'Informe uma cor válida.';
        if ($icon !== '' && !in_array($icon, self::ICONS, true)) $this->errors['icone'] = 'Selecione um ícone válido.';
        $this->failWhenInvalid();
        return ['nome' => $name, 'cor' => $color, 'icone' => $icon];
    }

    /** @return list<string> */
    public static function icons(): array { return self::ICONS; }
}
