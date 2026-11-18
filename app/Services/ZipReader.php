<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Leitor ZIP leve, em PHP puro.
 *
 * Alguns ambientes (XAMPP com php.ini mínimo) não têm a extensão Zip
 * habilitada. Como EPUB é um arquivo ZIP, precisamos de uma alternativa
 * para localizar a capa sem depender de extensões externas.
 */
final class ZipReader
{
    private string $data = '';

    /** @var array<string, array{method:int, compressedSize:int, uncompressedSize:int, localOffset:int}> */
    private array $entries = [];

    /** @var array<string, string> */
    private array $lowerMap = [];

    private bool $loaded = false;

    public function __construct(private readonly string $path)
    {
    }

    public function isOpen(): bool
    {
        $this->load();

        return $this->entries !== [];
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        $this->load();

        return array_keys($this->entries);
    }

    public function has(string $name): bool
    {
        $this->load();

        return $this->resolveName($name) !== null;
    }

    public function size(string $name): int
    {
        $this->load();
        $resolved = $this->resolveName($name);

        return $resolved === null ? 0 : $this->entries[$resolved]['uncompressedSize'];
    }

    public function read(string $name): ?string
    {
        $this->load();
        $resolved = $this->resolveName($name);

        if ($resolved === null) {
            return null;
        }

        $entry = $this->entries[$resolved];
        $offset = $entry['localOffset'];

        if (substr($this->data, $offset, 4) !== "PK\x03\x04") {
            return null;
        }

        $header = @unpack('vversion/vflags/vmethod/vtime/vdate/Vcrc/VcompressedSize/VuncompressedSize/vnameLen/vextraLen', substr($this->data, $offset + 4, 26));

        if ($header === false) {
            return null;
        }

        $dataStart = $offset + 30 + (int) $header['nameLen'] + (int) $header['extraLen'];
        $compressedSize = $entry['compressedSize'];
        $compressed = substr($this->data, $dataStart, $compressedSize);

        if ($compressed === false) {
            return null;
        }

        if ($entry['method'] === 0) {
            return $compressed;
        }

        if ($entry['method'] !== 8) {
            return null;
        }

        $inflated = @gzinflate($compressed);
        if ($inflated !== false) {
            return $inflated;
        }

        $decoded = @zlib_decode($compressed);

        return $decoded === false ? null : $decoded;
    }

    /**
     * Encontra um nome de entrada tolerando diferenças de caixa e prefixos.
     */
    public function find(string $needle): ?string
    {
        $this->load();
        $needle = $this->normalise($needle);
        $lowerNeedle = strtolower($needle);

        foreach ($this->lowerMap as $lower => $original) {
            if ($lower === $lowerNeedle) {
                return $original;
            }
        }

        foreach ($this->lowerMap as $lower => $original) {
            if (str_ends_with($lower, '/' . $lowerNeedle)) {
                return $original;
            }
        }

        return null;
    }

    private function resolveName(string $name): ?string
    {
        $name = $this->normalise($name);

        if (isset($this->entries[$name])) {
            return $name;
        }

        return $this->lowerMap[strtolower($name)] ?? null;
    }

    private function normalise(string $name): string
    {
        return ltrim(str_replace('\\', '/', $name), '/');
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $data = @file_get_contents($this->path, false, null, 0, 120 * 1024 * 1024);
        if ($data === false || $data === '') {
            return;
        }

        $this->data = $data;

        $eocd = strrpos($data, "PK\x05\x06");
        if ($eocd === false) {
            return;
        }

        $summary = @unpack('vdisk/vcdDisk/vdiskEntries/ventries/Vsize/Voffset', substr($data, $eocd + 4, 16));
        if ($summary === false) {
            return;
        }

        $total = (int) $summary['entries'];
        $offset = (int) $summary['offset'];

        if ($total === 0 || $total === 0xFFFF || $offset === 0xFFFFFFFF) {
            return;
        }

        for ($i = 0; $i < $total; $i++) {
            if (substr($data, $offset, 4) !== "PK\x01\x02") {
                break;
            }

            $header = @unpack(
                'vversionMade/vversionNeed/vflags/vmethod/vtime/vdate/Vcrc/VcompressedSize/VuncompressedSize/vnameLen/vextraLen/vcommentLen/vdiskStart/vinternalAttr/VexternalAttr/VlocalOffset',
                substr($data, $offset + 4, 42)
            );

            if ($header === false) {
                break;
            }

            $nameLen = (int) $header['nameLen'];
            $extraLen = (int) $header['extraLen'];
            $commentLen = (int) $header['commentLen'];
            $name = substr($data, $offset + 46, $nameLen);

            if ($name === false || $name === '') {
                break;
            }

            if (!str_ends_with($name, '/')) {
                $normalised = $this->normalise($name);
                $this->entries[$normalised] = [
                    'method' => (int) $header['method'],
                    'compressedSize' => (int) $header['compressedSize'],
                    'uncompressedSize' => (int) $header['uncompressedSize'],
                    'localOffset' => (int) $header['localOffset'],
                ];
                $this->lowerMap[strtolower($normalised)] = $normalised;
            }

            $offset += 46 + $nameLen + $extraLen + $commentLen;
        }
    }
}
