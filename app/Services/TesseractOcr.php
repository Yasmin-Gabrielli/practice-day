<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Wrapper do Tesseract CLI para reconhecimento de texto (OCR).
 * Detecta o binário em TESSERACT_PATH, nos caminhos padrão e no PATH.
 */
final class TesseractOcr
{
    private const SUPPORTED_LANGS = ['por', 'eng', 'spa'];
    private const DEFAULT_LANG = 'por';
    private const TIMEOUT_SECONDS = 60;

    private static ?string $binary = null;
    private static bool $probed = false;

    public static function available(): bool
    {
        return self::binary() !== null;
    }

    public static function binary(): ?string
    {
        if (self::$probed) {
            return self::$binary;
        }
        self::$probed = true;

        foreach (self::candidates() as $candidate) {
            $isPath = str_contains($candidate, '/') || str_contains($candidate, '\\');
            if ($isPath && !is_file($candidate)) {
                continue;
            }
            [$code] = self::run([$candidate, '--version']);
            if ($code === 0) {
                self::$binary = $candidate;
                return self::$binary;
            }
        }

        self::$binary = null;
        return null;
    }

    /** @return list<string> */
    private static function candidates(): array
    {
        $candidates = [];

        $env = getenv('TESSERACT_PATH');
        if (is_string($env) && trim($env) !== '') {
            $candidates[] = trim($env);
        }

        $candidates[] = 'tesseract';

        if (DIRECTORY_SEPARATOR === '\\') {
            $localAppData = getenv('LOCALAPPDATA');
            if (is_string($localAppData) && $localAppData !== '') {
                $candidates[] = $localAppData . '\Programs\Tesseract-OCR\tesseract.exe';
            }
            $candidates[] = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
            $candidates[] = 'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe';
        } else {
            $candidates[] = '/usr/bin/tesseract';
            $candidates[] = '/usr/local/bin/tesseract';
            $candidates[] = '/opt/homebrew/bin/tesseract';
        }

        return $candidates;
    }

    public static function recognize(string $imagePath, string $lang = self::DEFAULT_LANG): ?string
    {
        $binary = self::binary();
        if ($binary === null || !is_file($imagePath)) {
            return null;
        }

        if (!in_array($lang, self::SUPPORTED_LANGS, true)) {
            $lang = self::DEFAULT_LANG;
        }

        [$code, $output] = self::run([$binary, $imagePath, 'stdout', '-l', $lang, '--psm', '6']);
        if ($code !== 0) {
            return null;
        }

        $text = trim($output);
        return $text === '' ? null : $text;
    }

    /**
     * @param list<string> $command
     * @return array{0: int, 1: string}
     */
    private static function run(array $command): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptors, $pipes, null, null);
        if (!is_resource($process) || !isset($pipes[1], $pipes[2]) || !is_resource($pipes[1]) || !is_resource($pipes[2])) {
            if (is_resource($process)) proc_close($process);
            return [1, ''];
        }

        if (is_resource($pipes[0])) fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $error = '';
        $deadline = microtime(true) + self::TIMEOUT_SECONDS;
        $stdoutOpen = true;
        $stderrOpen = true;

        while (($stdoutOpen || $stderrOpen) && microtime(true) < $deadline) {
            $read = [];
            if ($stdoutOpen) $read[] = $pipes[1];
            if ($stderrOpen) $read[] = $pipes[2];
            if ($read !== []) {
                $write = null;
                $except = null;
                @stream_select($read, $write, $except, 0, 200000);
            }
            if ($stdoutOpen) {
                $chunk = stream_get_contents($pipes[1]);
                if ($chunk === false || $chunk === '') {
                    if (feof($pipes[1])) $stdoutOpen = false;
                } else {
                    $output .= $chunk;
                }
            }
            if ($stderrOpen) {
                $chunk = stream_get_contents($pipes[2]);
                if ($chunk === false || $chunk === '') {
                    if (feof($pipes[2])) $stderrOpen = false;
                } else {
                    $error .= $chunk;
                }
            }
        }

        $code = proc_close($process);
        if (is_resource($pipes[1])) fclose($pipes[1]);
        if (is_resource($pipes[2])) fclose($pipes[2]);

        if ($code !== 0 && $code !== -1) {
            return [$code, $output !== '' ? $output : $error];
        }

        return [$code, $output !== '' ? $output : $error];
    }
}
