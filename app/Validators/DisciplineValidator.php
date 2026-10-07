<?php

declare(strict_types=1);

namespace App\Validators;

final class DisciplineValidator extends Validator
{
    private const ICONS = [
        'bi-book', 'bi-book-half', 'bi-bookshelf', 'bi-journal-text', 'bi-journal-bookmark',
        'bi-mortarboard', 'bi-pencil', 'bi-pencil-square',
        'bi-calculator', 'bi-code-slash', 'bi-braces', 'bi-terminal', 'bi-rulers',
        'bi-globe-americas', 'bi-translate', 'bi-compass', 'bi-geo-alt', 'bi-airplane',
        'bi-palette', 'bi-music-note-beamed', 'bi-camera', 'bi-film', 'bi-mic',
        'bi-cpu', 'bi-gear', 'bi-laptop', 'bi-graph-up', 'bi-bar-chart-line',
        'bi-lightbulb', 'bi-rocket-takeoff', 'bi-star', 'bi-award', 'bi-people',
        'bi-heart-pulse', 'bi-activity', 'bi-droplet', 'bi-tree', 'bi-clock',
        'bi-alarm', 'bi-calendar3', 'bi-briefcase', 'bi-cart', 'bi-bag',
        'bi-building', 'bi-controller', 'bi-bookmark', 'bi-diagram-3', 'bi-clipboard-data',
        'bi-clipboard2-pulse', 'bi-lightning-charge', 'bi-puzzle', 'bi-qr-code',
        'bi-emoji-smile', 'bi-bandaid', 'bi-sun', 'bi-moon-stars',
    ];
    private const DEFAULT_ICON = 'bi-mortarboard';

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

    /** Retorna um ícone válido da lista ou o padrão quando o valor está vazio/inválido. */
    public static function safeIcon(?string $icon): string
    {
        $value = trim((string) $icon);

        return in_array($value, self::ICONS, true) ? $value : self::DEFAULT_ICON;
    }
}
