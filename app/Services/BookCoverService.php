<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Detecção e cache de capas de livros.
 *
 * - EPUB: extrai a imagem de capa diretamente do arquivo (ZIP + OPF).
 * - PDF: verifica capas já geradas no navegador e armazenadas em cache.
 * - Outros formatos: usa a própria imagem quando aplicável.
 *
 * Nenhuma capa é obrigatória: quando não há capa, geramos um SVG
 * legível com o título do material, garantindo sempre uma prévia.
 */
final class BookCoverService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    /** @var array<string, array{path:string, mime:string, ext:string}|null> */
    private static array $memoryCache = [];

    public function __construct(private readonly array $config)
    {
    }

    public static function isCached(string $fileId): bool
    {
        $safeId = self::safeId($fileId);
        if ($safeId === '') {
            return false;
        }

        $directory = dirname(__DIR__, 2) . '/storage/covers';

        foreach (self::IMAGE_EXTENSIONS as $extension) {
            if (is_file($directory . '/' . $safeId . '.' . $extension)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $file
     * @return array{path:string, mime:string, ext:string}|null
     */
    public function getCover(array $file, string $physicalPath): ?array
    {
        $fileId = (string) ($file['id'] ?? '');
        $ext = strtolower((string) ($file['extensao'] ?? ''));

        if ($fileId === '' || !is_file($physicalPath)) {
            return null;
        }

        if ($ext !== 'pdf' && isset(self::$memoryCache[$fileId])) {
            return self::$memoryCache[$fileId];
        }

        $cached = $this->cachedCover($fileId);
        if ($cached !== null) {
            self::$memoryCache[$fileId] = $cached;

            return $cached;
        }

        if ($ext === 'epub') {
            $extracted = $this->extractEpubCover($physicalPath);
            if ($extracted !== null) {
                $saved = $this->writeCover($fileId, $extracted['binary'], $extracted['mime']);
                if ($saved !== null) {
                    self::$memoryCache[$fileId] = $saved;

                    return $saved;
                }
            }
        }

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            $mime = $this->mimeFromPath($physicalPath);
            if ($mime !== null) {
                $result = ['path' => $physicalPath, 'mime' => $mime, 'ext' => $ext];
                self::$memoryCache[$fileId] = $result;

                return $result;
            }
        }

        self::$memoryCache[$fileId] = null;

        return null;
    }

    /**
     * Salva uma capa gerada no navegador (PDF.js) no cache do servidor.
     */
    public function saveDataUrl(string $fileId, string $dataUrl): ?array
    {
        $safeId = self::safeId($fileId);
        if ($safeId === '' || $dataUrl === '') {
            return null;
        }

        if (!preg_match('#^data:(image/(?:jpeg|jpg|png|webp));base64,([A-Za-z0-9+/=\s]+)$#i', $dataUrl, $match)) {
            return null;
        }

        $binary = base64_decode(preg_replace('/\s+/', '', $match[2]) ?? '', true);
        if ($binary === false || $binary === '' || strlen($binary) > 8 * 1024 * 1024) {
            return null;
        }

        $info = @getimagesizefromstring($binary);
        if ($info === false || (int) $info[0] < 60 || (int) $info[1] < 80) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->buffer($binary));

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $saved = $this->writeCover($safeId, $binary, $mime);
        if ($saved !== null) {
            self::$memoryCache[$fileId] = $saved;
        }

        return $saved;
    }

    public function placeholderSvg(string $title, string $type = 'file'): string
    {
        $title = trim($title);
        if ($title === '') {
            $title = 'Material de estudo';
        }

        $title = (string) preg_replace('/\.[a-z0-9]{2,5}$/i', '', $title);
        $type = strtolower($type);
        [$start, $end, $label] = match ($type) {
            'pdf' => ['#b91c1c', '#7f1d1d', 'PDF'],
            'epub' => ['#2563eb', '#1e3a8a', 'EPUB'],
            default => ['#334155', '#0f172a', strtoupper($type !== '' ? $type : 'ARQUIVO')],
        };

        $lines = $this->wrapTitle($title, 4, 20);
        $tspans = '';
        foreach ($lines as $index => $line) {
            $y = 520 + ($index * 46);
            $tspans .= '<tspan x="56" y="' . $y . '">' . htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</tspan>';
        }

        $safeLabel = htmlspecialchars(mb_strtoupper($label), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $safeFullTitle = htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900" role="img" aria-label="Capa de ' . $safeFullTitle . '">'
            . '<title>' . $safeFullTitle . '</title>'
            . '<defs>'
            . '<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . $start . '"/><stop offset="1" stop-color="' . $end . '"/></linearGradient>'
            . '<linearGradient id="shine" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff" stop-opacity=".22"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/></linearGradient>'
            . '</defs>'
            . '<rect width="600" height="900" rx="18" fill="url(#bg)"/>'
            . '<rect x="24" y="24" width="552" height="852" rx="12" fill="none" stroke="#ffffff" stroke-opacity=".28" stroke-width="2"/>'
            . '<path d="M430 120h96v96l-96-96z" fill="url(#shine)"/>'
            . '<g fill="none" stroke="#ffffff" stroke-opacity=".82" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">'
            . '<path d="M56 180h150a34 34 0 0 1 34 34v52a34 34 0 0 0 34 34H56z" transform="translate(0 -18)"/>'
            . '<path d="M56 180h150a34 34 0 0 1 34 34v52a34 34 0 0 0 34 34H56z" transform="translate(0 54)"/>'
            . '</g>'
            . '<rect x="56" y="392" width="126" height="32" rx="16" fill="#ffffff" fill-opacity=".18"/>'
            . '<text x="119" y="414" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="17" font-weight="700" fill="#ffffff" letter-spacing="2">' . $safeLabel . '</text>'
            . '<text font-family="Segoe UI, Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff">' . $tspans . '</text>'
            . '<text x="56" y="838" font-family="Segoe UI, Arial, sans-serif" font-size="16" font-weight="700" fill="#ffffff" fill-opacity=".72" letter-spacing="2">PRACTICEDAY</text>'
            . '</svg>';
    }

    /**
     * @return array{binary:string, mime:string}|null
     */
    private function extractEpubCover(string $path): ?array
    {
        $zip = new ZipReader($path);

        if (!$zip->isOpen()) {
            return null;
        }

        $containerPath = $zip->find('META-INF/container.xml');
        $container = $containerPath !== null ? $zip->read($containerPath) : null;

        if ($container === null) {
            return $this->guessCoverFromArchive($zip);
        }

        $rootFile = $this->firstXPathAttribute($container, '//*[local-name()="rootfile"]', 'full-path');
        if ($rootFile === null || $rootFile === '') {
            return $this->guessCoverFromArchive($zip);
        }

        $opfPath = $zip->find($rootFile);
        $opf = $opfPath !== null ? $zip->read($opfPath) : null;

        if ($opf === null) {
            return $this->guessCoverFromArchive($zip);
        }

        $document = new DOMDocument();
        if (!@$document->loadXML($opf, LIBXML_NONET | LIBXML_NOCDATA)) {
            return $this->guessCoverFromArchive($zip);
        }

        $xpath = new DOMXPath($document);

        $coverId = '';
        foreach ($xpath->query('//*[local-name()="metadata"]//*[local-name()="meta"]') ?: [] as $meta) {
            if (!$meta instanceof DOMElement) {
                continue;
            }
            if (strtolower(trim($meta->getAttribute('name'))) === 'cover') {
                $coverId = trim($meta->getAttribute('content'));
                break;
            }
        }

        $items = [];
        foreach ($xpath->query('//*[local-name()="manifest"]/*[local-name()="item"]') ?: [] as $item) {
            if (!$item instanceof DOMElement) {
                continue;
            }

            $href = trim($item->getAttribute('href'));
            if ($href === '') {
                continue;
            }

            $items[] = [
                'id' => trim($item->getAttribute('id')),
                'href' => $href,
                'type' => strtolower(trim($item->getAttribute('media-type'))),
                'properties' => strtolower(trim($item->getAttribute('properties'))),
            ];
        }

        $candidates = [];

        foreach ($items as $item) {
            if (str_contains($item['properties'], 'cover-image')) {
                $candidates[] = $item;
                break;
            }
        }

        if ($candidates === [] && $coverId !== '') {
            foreach ($items as $item) {
                if ($item['id'] === $coverId) {
                    $candidates[] = $item;
                    break;
                }
            }
        }

        if ($candidates === []) {
            foreach ($items as $item) {
                if (str_contains(strtolower($item['href']), 'cover') && $this->isImageType($item['type'], $item['href'])) {
                    $candidates[] = $item;
                    break;
                }
            }
        }

        if ($candidates === []) {
            foreach ($items as $item) {
                if ($this->isImageType($item['type'], $item['href'])) {
                    $candidates[] = $item;
                    break;
                }
            }
        }

        $opfDirectory = $this->zipDirname($opfPath ?? $rootFile);

        foreach ($candidates as $candidate) {
            $resolved = $this->resolveZipPath($opfDirectory, $candidate['href']);
            $result = $this->readImageOrWrapper($zip, $resolved);

            if ($result !== null) {
                return $result;
            }
        }

        return $this->guessCoverFromArchive($zip);
    }

    /**
     * @return array{binary:string, mime:string}|null
     */
    private function readImageOrWrapper(ZipReader $zip, string $entryPath): ?array
    {
        $name = $zip->find($entryPath);
        if ($name === null) {
            return null;
        }

        $binary = $zip->read($name);
        if ($binary === null || $binary === '') {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->buffer($binary));

        if (str_starts_with($mime, 'image/') && !str_contains($mime, 'svg')) {
            return ['binary' => $binary, 'mime' => $mime];
        }

        if (str_contains($mime, 'svg') || preg_match('/<svg[\s>]/i', substr($binary, 0, 4000)) === 1) {
            $reference = $this->firstImageReference($binary);
            if ($reference !== null) {
                $resolved = $this->resolveZipPath($this->zipDirname($name), $reference);
                $nested = $zip->find($resolved);
                if ($nested !== null) {
                    $nestedBinary = $zip->read($nested);
                    if ($nestedBinary !== null) {
                        $nestedMime = strtolower((string) $finfo->buffer($nestedBinary));
                        if (str_starts_with($nestedMime, 'image/') && !str_contains($nestedMime, 'svg')) {
                            return ['binary' => $nestedBinary, 'mime' => $nestedMime];
                        }
                    }
                }
            }

            return ['binary' => $binary, 'mime' => 'image/svg+xml'];
        }

        if (str_contains($mime, 'html') || str_contains($mime, 'xml')) {
            $reference = $this->firstImageReference($binary);
            if ($reference !== null) {
                $resolved = $this->resolveZipPath($this->zipDirname($name), $reference);

                return $this->readImageOrWrapper($zip, $resolved);
            }
        }

        return null;
    }

    /**
     * @return array{binary:string, mime:string}|null
     */
    private function guessCoverFromArchive(ZipReader $zip): ?array
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $candidates = [];

        foreach ($zip->names() as $name) {
            if (preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $name) !== 1) {
                continue;
            }

            $size = $zip->size($name);
            if ($size < 2048) {
                continue;
            }

            $score = str_contains(strtolower($name), 'cover') ? 2_000_000_000 : 0;
            $candidates[$name] = $score + $size;
        }

        arsort($candidates);

        foreach (array_slice(array_keys($candidates), 0, 5) as $name) {
            $binary = $zip->read($name);
            if ($binary === null || $binary === '') {
                continue;
            }

            $mime = strtolower((string) $finfo->buffer($binary));
            if (str_contains($mime, 'svg')) {
                $reference = $this->firstImageReference($binary);
                if ($reference !== null) {
                    $nested = $zip->find($this->resolveZipPath($this->zipDirname($name), $reference));
                    if ($nested !== null) {
                        $nestedBinary = $zip->read($nested);
                        if ($nestedBinary !== null) {
                            $nestedMime = strtolower((string) $finfo->buffer($nestedBinary));
                            if (str_starts_with($nestedMime, 'image/')) {
                                return ['binary' => $nestedBinary, 'mime' => $nestedMime];
                            }
                        }
                    }
                }

                return ['binary' => $binary, 'mime' => 'image/svg+xml'];
            }

            if (str_starts_with($mime, 'image/')) {
                return ['binary' => $binary, 'mime' => $mime];
            }
        }

        return null;
    }

    private function isImageType(string $type, string $href): bool
    {
        if (str_starts_with($type, 'image/')) {
            return true;
        }

        return preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $href) === 1;
    }

    private function firstImageReference(string $html): ?string
    {
        $patterns = [
            '/<image[^>]+(?:xlink:href|href)\s*=\s*["\']([^"\']+)["\']/i',
            '/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i',
            '/url\(\s*["\']?([^"\')]+)["\']?\s*\)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match) === 1) {
                $reference = trim((string) $match[1]);
                if ($reference !== '' && !str_starts_with(strtolower($reference), 'data:')) {
                    return $reference;
                }
            }
        }

        return null;
    }

    private function firstXPathAttribute(string $xml, string $query, string $attribute): ?string
    {
        $document = new DOMDocument();
        if (!@$document->loadXML($xml, LIBXML_NONET | LIBXML_NOCDATA)) {
            return null;
        }

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query($query);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $first = $nodes->item(0);

        return $first instanceof DOMElement ? $first->getAttribute($attribute) : null;
    }

    private function resolveZipPath(string $directory, string $href): string
    {
        $href = rawurldecode($href);
        $href = preg_split('/[?#]/', $href)[0] ?? $href;
        $href = str_replace('\\', '/', $href);

        if (str_starts_with($href, '/')) {
            return ltrim($href, '/');
        }

        $parts = $directory === '' || $directory === '.' ? [] : explode('/', trim($directory, '/'));

        foreach (explode('/', $href) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);
                continue;
            }

            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    private function zipDirname(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $directory = dirname($path);

        return $directory === '.' ? '' : $directory;
    }

    /**
     * @return array{path:string, mime:string, ext:string}|null
     */
    private function writeCover(string $fileId, string $binary, string $mime): ?array
    {
        $extension = $this->extensionForMime($mime);
        if ($extension === null) {
            return null;
        }

        $directory = $this->coversDirectory();
        if ($directory === null) {
            return null;
        }

        $target = $directory . '/' . self::safeId($fileId) . '.' . $extension;
        $temporary = $target . '.tmp-' . bin2hex(random_bytes(4));

        if (@file_put_contents($temporary, $binary) === false) {
            return null;
        }

        if (!@rename($temporary, $target)) {
            @unlink($target);
            if (!@rename($temporary, $target)) {
                @unlink($temporary);

                return null;
            }
        }

        return ['path' => $target, 'mime' => $mime, 'ext' => $extension];
    }

    /**
     * @return array{path:string, mime:string, ext:string}|null
     */
    private function cachedCover(string $fileId): ?array
    {
        $safeId = self::safeId($fileId);
        if ($safeId === '') {
            return null;
        }

        $directory = dirname(__DIR__, 2) . '/storage/covers';

        foreach (self::IMAGE_EXTENSIONS as $extension) {
            $path = $directory . '/' . $safeId . '.' . $extension;
            if (!is_file($path)) {
                continue;
            }

            $mime = $this->mimeFromPath($path);
            if ($mime !== null) {
                return ['path' => $path, 'mime' => $mime, 'ext' => $extension];
            }
        }

        return null;
    }

    private function coversDirectory(): ?string
    {
        $directory = dirname(__DIR__, 2) . '/storage/covers';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        return $directory;
    }

    private function mimeFromPath(string $path): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->file($path));

        return $this->extensionForMime($mime) !== null ? $mime : null;
    }

    private function extensionForMime(string $mime): ?string
    {
        return match (strtolower($mime)) {
            'image/jpeg', 'image/jpg', 'image/pjpeg' => 'jpg',
            'image/png', 'image/x-png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml', 'image/svg' => 'svg',
            default => null,
        };
    }

    private static function safeId(string $fileId): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9_-]/', '', $fileId);
    }

    /**
     * @return list<string>
     */
    private function wrapTitle(string $title, int $maxLines, int $maxChars): array
    {
        $words = preg_split('/\s+/u', $title) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;

            if (mb_strlen($candidate) > $maxChars && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }

            if (count($lines) >= $maxLines) {
                break;
            }
        }

        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        if ($lines === []) {
            $lines[] = mb_substr($title, 0, $maxChars);
        }

        $lines = array_map(
            static fn (string $value): string => mb_strlen($value) > $maxChars
                ? rtrim(mb_substr($value, 0, $maxChars - 1)) . '…'
                : $value,
            $lines
        );

        if (count($lines) === $maxLines && mb_strlen($title) > mb_strlen(implode(' ', $lines))) {
            $last = $lines[$maxLines - 1];
            $lines[$maxLines - 1] = mb_strlen($last) > $maxChars - 1
                ? rtrim(mb_substr($last, 0, $maxChars - 2)) . '…'
                : $last . '…';
        }

        return $lines;
    }
}
