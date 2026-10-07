<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Conversão básica DOCX → HTML via ZipArchive + XML (sem dependências externas).
 * Para formatação avançada, instale PHPWord: composer require phpoffice/phpword
 */
final class DocxHtmlConverter
{
    /**
     * @return array{html: string, engine: string, message: string|null}
     */
    public function convertFile(string $absolutePath): array
    {
        if (!is_readable($absolutePath)) {
            return [
                'html'    => '',
                'engine'  => 'none',
                'message' => 'Arquivo DOCX não legível.',
            ];
        }

        if (class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            return $this->convertWithPhpWord($absolutePath);
        }

        return $this->convertWithZipXml($absolutePath);
    }

    /**
     * @return array{html: string, engine: string, message: string|null}
     */
    private function convertWithPhpWord(string $path): array
    {
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $writer  = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            ob_start();
            $writer->save('php://output');
            $html = (string) ob_get_clean();

            return [
                'html'    => $this->sanitizeHtml($html),
                'engine'  => 'phpword',
                'message' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'html'    => '',
                'engine'  => 'phpword',
                'message' => 'Falha ao converter com PHPWord: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{html: string, engine: string, message: string|null}
     */
    private function convertWithZipXml(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return [
                'html'    => '',
                'engine'  => 'zip',
                'message' => 'Extensão ZIP do PHP não está habilitada.',
            ];
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [
                'html'    => '',
                'engine'  => 'zip',
                'message' => 'Não foi possível abrir o arquivo DOCX.',
            ];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || $xml === '') {
            return [
                'html'    => '',
                'engine'  => 'zip',
                'message' => 'Conteúdo word/document.xml ausente no DOCX.',
            ];
        }

        $dom = new \DOMDocument();
        $loaded = @$dom->loadXML($xml);
        if ($loaded === false) {
            return [
                'html'    => '',
                'engine'  => 'zip',
                'message' => 'XML do documento inválido.',
            ];
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = $xpath->query('//w:p');
        $htmlParts  = [];

        if ($paragraphs !== false) {
            foreach ($paragraphs as $p) {
                $textNodes = $xpath->query('.//w:t', $p);
                $line      = '';
                if ($textNodes !== false) {
                    foreach ($textNodes as $t) {
                        $line .= $t->textContent;
                    }
                }
                $line = trim($line);
                if ($line === '') {
                    $htmlParts[] = '<p class="docx-empty-line">&nbsp;</p>';
                } else {
                    $htmlParts[] = '<p>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
                }
            }
        }

        $html = implode("\n", $htmlParts);

        return [
            'html'    => $this->sanitizeHtml($html),
            'engine'  => 'zip-xml',
            'message' => $html === ''
                ? 'Documento vazio ou sem texto extraível. Para layouts complexos: composer require phpoffice/phpword'
                : 'Visualização simplificada (texto/parágrafos). Para formatação completa: composer require phpoffice/phpword',
        ];
    }

    private function sanitizeHtml(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><table><tr><td><th><thead><tbody>');

        return trim($html);
    }
}
