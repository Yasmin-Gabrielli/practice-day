<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Formata o nome de um arquivo para exibição, sem a extensão.
 *
 * O nome original continua intacto no banco (download, histórico e busca);
 * aqui apenas removemos o sufixo correspondente à extensão conhecida.
 */
final class FileDisplay
{
    public static function name(?string $nomeOriginal, ?string $extensao = null): string
    {
        $nome = trim((string) $nomeOriginal);
        if ($nome === '') {
            return 'Arquivo sem nome';
        }

        $ext = strtolower(trim((string) $extensao));
        if ($ext === '') {
            $detected = strtolower((string) pathinfo($nome, PATHINFO_EXTENSION));
            if ($detected !== '') {
                $ext = $detected;
            }
        }

        if ($ext !== '') {
            $suffix = '.' . $ext;
            $suffixLength = strlen($suffix);
            if (
                strlen($nome) > $suffixLength
                && strcasecmp(substr($nome, -$suffixLength), $suffix) === 0
            ) {
                $nome = substr($nome, 0, -$suffixLength);
            }
        }

        return $nome !== '' ? $nome : 'Arquivo sem nome';
    }
}
