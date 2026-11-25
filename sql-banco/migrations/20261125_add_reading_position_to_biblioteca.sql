-- Posição exata de leitura.
-- O CFI do EPUB deixa de ficar escondido em marcadores_livro e passa a ter
-- coluna própria, sem truncamento. PDFs também guardam o deslocamento dentro
-- da página e o zoom usados quando a leitura foi interrompida.

ALTER TABLE `biblioteca`
  ADD COLUMN `cfi` TEXT NULL AFTER `progresso_porcentagem`,
  ADD COLUMN `posicao_scroll` INT(11) NOT NULL DEFAULT 0 AFTER `cfi`,
  ADD COLUMN `zoom` DECIMAL(5,2) NOT NULL DEFAULT 1.00 AFTER `posicao_scroll`;

-- Aproveita posições antigas que estavam no marcador oculto __cfi__.
UPDATE `biblioteca` b
INNER JOIN (
    SELECT biblioteca_id, MIN(SUBSTRING(titulo, 8)) AS cfi
    FROM `marcadores_livro`
    WHERE LEFT(titulo, 7) = '__cfi__'
    GROUP BY biblioteca_id
) m ON m.biblioteca_id = b.id
SET b.cfi = m.cfi
WHERE b.cfi IS NULL;

DELETE FROM `marcadores_livro` WHERE LEFT(titulo, 7) = '__cfi__';
