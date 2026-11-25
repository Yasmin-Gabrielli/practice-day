<?php
use App\Helpers\FileDisplay;
use App\Helpers\Url;

$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1048576) {
        return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }

    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
};

$extLabel = strtoupper((string) ($file['extensao'] ?? ''));
$disciplina = (string) ($file['disciplina_nome'] ?? 'Sem disciplina');
$fileName = FileDisplay::name($file['nome_original'] ?? null, $file['extensao'] ?? null);
$progresso = is_array($library['progresso'] ?? null) ? $library['progresso'] : null;
$initialPage = max(1, (int) ($progresso['pagina_atual'] ?? 1));
$totalPages = max(0, (int) ($progresso['total_paginas'] ?? 0));
$initialPercent = max(0, min(100, (float) ($progresso['progresso_porcentagem'] ?? 0)));

$extraStyles = [Url::asset('assets/css/file-viewer.css')];

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell file-viewer-page">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content reader-page">
            <a class="reader-skip-link" href="#reader-stage">Pular para o conteúdo do documento</a>

            <header class="reader-heading">
                <div class="reader-heading-main">
                    <a class="back-link" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>Voltar
                    </a>
                    <div class="reader-heading-text">
                        <p class="eyebrow">Modo de leitura</p>
                        <h1 class="reader-title"><?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>

                <div class="reader-heading-meta" aria-label="Informações do arquivo">
                    <span class="reader-chip"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><?= htmlspecialchars($extLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="reader-chip"><i class="bi bi-hdd" aria-hidden="true"></i><?= $formatBytes((int) ($file['tamanho_bytes'] ?? 0)) ?></span>
                    <span class="reader-chip"><i class="bi bi-mortarboard" aria-hidden="true"></i><?= htmlspecialchars($disciplina, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($viewerType === 'pdf' || $viewerType === 'epub'): ?>
                        <span class="reader-chip reader-chip-progress"><i class="bi bi-bookmark-check" aria-hidden="true"></i><span data-reader-progress-label><?= number_format($initialPercent, 1, ',', '.') ?>%</span></span>
                    <?php endif; ?>
                </div>

                <div class="reader-heading-actions">
                    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-download" aria-hidden="true"></i><span>Download</span>
                    </a>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-x-lg" aria-hidden="true"></i><span>Fechar</span>
                    </a>
                </div>
            </header>

            <section class="reader-shell" data-reader-shell data-reader-type="<?= htmlspecialchars($viewerType, ENT_QUOTES, 'UTF-8') ?>" data-flow="paged" data-reader-theme="light" aria-label="Leitor de documentos">
                <div class="reader-toolbar" role="toolbar" aria-label="Ferramentas de leitura">
                    <div class="reader-toolbar-group">
                        <button type="button" class="reader-btn" data-reader-panel-toggle aria-expanded="false" aria-controls="reader-panel" title="Abrir painel de leitura" aria-label="Abrir painel de leitura">
                            <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
                        </button>

                        <?php if ($viewerType === 'pdf' || $viewerType === 'epub'): ?>
                            <button type="button" class="reader-btn" data-reader-prev title="Página anterior (seta esquerda)" aria-label="Página anterior" aria-keyshortcuts="ArrowLeft PageUp">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-reader-next title="Próxima página (seta direita)" aria-label="Próxima página" aria-keyshortcuts="ArrowRight PageDown">
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>

                        <?php if ($viewerType === 'pdf'): ?>
                            <div class="reader-page-control">
                                <label class="visually-hidden" for="pdf-page-input">Ir para a página</label>
                                <input id="pdf-page-input" type="number" inputmode="numeric" min="1" value="<?= $initialPage ?>" data-pdf-page-input autocomplete="off">
                                <span class="reader-page-total">/ <span data-pdf-total><?= $totalPages > 0 ? $totalPages : '—' ?></span></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($viewerType === 'epub'): ?>
                            <span class="reader-location" data-epub-location>Preparando leitura…</span>
                        <?php endif; ?>
                    </div>

                    <div class="reader-toolbar-group reader-toolbar-group-end">
                        <?php if ($viewerType === 'pdf'): ?>
                            <button type="button" class="reader-btn" data-pdf-zoom-out title="Diminuir zoom (-)" aria-label="Diminuir zoom" aria-keyshortcuts="-">
                                <i class="bi bi-zoom-out" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-pdf-zoom-in title="Aumentar zoom (+)" aria-label="Aumentar zoom" aria-keyshortcuts="+">
                                <i class="bi bi-zoom-in" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-pdf-fit title="Ajustar à largura" aria-label="Ajustar página à largura">
                                <i class="bi bi-arrows-angle-expand" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn reader-btn-optional" data-pdf-rotate title="Girar página 90°" aria-label="Girar página 90 graus">
                                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                            </button>
                        <?php elseif ($viewerType === 'epub'): ?>
                            <button type="button" class="reader-btn" data-epub-toc-toggle title="Sumário" aria-label="Abrir sumário">
                                <i class="bi bi-list-nested" aria-hidden="true"></i>
                            </button>
                        <?php elseif ($viewerType === 'image'): ?>
                            <button type="button" class="reader-btn" data-img-zoom-out title="Diminuir zoom" aria-label="Diminuir zoom">
                                <i class="bi bi-zoom-out" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-img-zoom-in title="Aumentar zoom" aria-label="Aumentar zoom">
                                <i class="bi bi-zoom-in" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-img-zoom-reset title="Tamanho original" aria-label="Restaurar tamanho original">
                                <i class="bi bi-aspect-ratio" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-img-rotate title="Girar imagem" aria-label="Girar imagem 90 graus">
                                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>

                        <?php if ($viewerType === 'pdf' || $viewerType === 'epub'): ?>
                            <button type="button" class="reader-btn" data-reader-flow-toggle title="Alternar entre modo paginado e rolagem" aria-label="Alternar modo de leitura" aria-pressed="false">
                                <i class="bi bi-arrows-move" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="reader-btn" data-reader-bookmark title="Criar marcador" aria-label="Criar marcador nesta posição">
                                <i class="bi bi-bookmark-plus" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>

                        <?php if ($viewerType === 'pdf'): ?>
                            <button type="button" class="reader-btn reader-btn-optional" data-pdf-print title="Imprimir" aria-label="Imprimir documento">
                                <i class="bi bi-printer" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>

                        <button type="button" class="reader-btn" data-reader-theme-toggle title="Alternar tema de leitura" aria-label="Alternar tema de leitura">
                            <i class="bi bi-circle-half" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-settings title="Ajustes de leitura" aria-label="Abrir ajustes de leitura">
                            <i class="bi bi-sliders" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-fullscreen title="Tela cheia (F)" aria-label="Entrar em tela cheia" aria-keyshortcuts="f">
                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="reader-body">
                    <aside class="reader-panel" id="reader-panel" data-reader-panel hidden aria-label="Painel de leitura">
                        <div class="reader-panel-tabs" role="tablist" aria-label="Seções do painel">
                            <button type="button" class="reader-panel-tab is-active" role="tab" aria-selected="true" aria-controls="reader-settings-section" data-reader-tab="settings">Leitura</button>
                            <?php if ($viewerType === 'pdf'): ?>
                                <button type="button" class="reader-panel-tab" role="tab" aria-selected="false" aria-controls="reader-marks-section" data-reader-tab="marks">Marcadores</button>
                            <?php endif; ?>
                        </div>

                        <section class="reader-panel-section" id="reader-settings-section" role="tabpanel" data-reader-section="settings">
                            <h2 class="reader-panel-title">Aparência</h2>
                            <div class="reader-theme-options" role="group" aria-label="Tema de leitura">
                                <button type="button" class="reader-theme-option" data-reader-theme-option="light" aria-pressed="false"><span class="reader-theme-swatch theme-light"></span>Claro</button>
                                <button type="button" class="reader-theme-option" data-reader-theme-option="sepia" aria-pressed="false"><span class="reader-theme-swatch theme-sepia"></span>Sépia</button>
                                <button type="button" class="reader-theme-option" data-reader-theme-option="dark" aria-pressed="false"><span class="reader-theme-swatch theme-dark"></span>Escuro</button>
                            </div>

                            <label class="reader-field" for="reader-font-range">
                                <span>Tamanho do texto</span>
                                <input id="reader-font-range" type="range" min="80" max="180" step="5" value="100" data-reader-font-range>
                            </label>

                            <label class="reader-field" for="reader-line-height">
                                <span>Espaçamento entre linhas</span>
                                <select id="reader-line-height" data-reader-line-height>
                                    <option value="1.45">Compacto</option>
                                    <option value="1.7" selected>Confortável</option>
                                    <option value="2">Amplo</option>
                                </select>
                            </label>

                            <label class="reader-field" for="reader-width-range">
                                <span>Largura da coluna</span>
                                <input id="reader-width-range" type="range" min="55" max="95" step="5" value="72" data-reader-width-range>
                            </label>

                            <?php if ($viewerType === 'pdf' || $viewerType === 'epub'): ?>
                                <label class="reader-field" for="reader-flow-select">
                                    <span>Modo de leitura</span>
                                    <select id="reader-flow-select" data-reader-flow-select>
                                        <option value="paged">Página a página</option>
                                        <option value="scroll">Rolagem contínua</option>
                                    </select>
                                </label>
                            <?php endif; ?>

                            <div class="reader-panel-hint">
                                <i class="bi bi-keyboard" aria-hidden="true"></i>
                                <p><button type="button" class="reader-link-button" data-reader-shortcuts>Abrir atalhos de teclado</button></p>
                            </div>
                        </section>

                        <?php if ($viewerType === 'pdf'): ?>
                            <section class="reader-panel-section" id="reader-marks-section" role="tabpanel" data-reader-section="marks" hidden>
                                <h2 class="reader-panel-title">Marcadores</h2>
                                <p class="reader-panel-empty" data-reader-bookmarks-empty>Nenhum marcador ainda. Use o botão de marcador na barra de ferramentas.</p>
                                <ul class="reader-panel-list" data-reader-bookmarks-list></ul>
                            </section>
                        <?php endif; ?>
                    </aside>

                    <div class="reader-stage" id="reader-stage" data-viewer-stage data-viewer-type="<?= htmlspecialchars($viewerType, ENT_QUOTES, 'UTF-8') ?>" tabindex="0" role="region" aria-label="Conteúdo do documento">
                        <?php if ($viewerType === 'unsupported'): ?>
                            <div class="file-viewer-message">
                                <i class="bi bi-file-earmark-lock" aria-hidden="true"></i>
                                <p>Este formato ainda não possui visualizador integrado. Use o download para abrir localmente.</p>
                                <a class="btn btn-primary" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-download me-1" aria-hidden="true"></i>Baixar arquivo</a>
                            </div>
                        <?php elseif ($viewerType === 'txt'): ?>
                            <?php if ($txtContent === null): ?>
                                <div class="file-viewer-message" role="alert">
                                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                    <p>Não foi possível carregar o conteúdo deste arquivo TXT.</p>
                                </div>
                            <?php else: ?>
                                <article class="txt-reader" data-text-reader>
                                    <pre class="txt-reader-content"><?= htmlspecialchars($txtContent, ENT_QUOTES, 'UTF-8') ?></pre>
                                </article>
                            <?php endif; ?>
                        <?php elseif ($viewerType === 'docx'): ?>
                            <?php
                            $docxHtml = (string) ($docxPreview['html'] ?? '');
                            $docxMsg  = $docxPreview['message'] ?? null;
                            $docxEngine = (string) ($docxPreview['engine'] ?? 'none');
                            ?>
                            <?php if ($docxHtml === ''): ?>
                                <div class="file-viewer-message">
                                    <i class="bi bi-filetype-docx" aria-hidden="true"></i>
                                    <p><?= htmlspecialchars((string) ($docxMsg ?: 'Visualização DOCX indisponível.'), ENT_QUOTES, 'UTF-8') ?></p>
                                    <a class="btn btn-primary" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-download me-1" aria-hidden="true"></i>Baixar arquivo</a>
                                </div>
                            <?php else: ?>
                                <?php if ($docxMsg): ?>
                                    <div class="file-viewer-inline-note" role="status">
                                        <?= htmlspecialchars($docxMsg, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($docxEngine === 'zip-xml'): ?>
                                            <span>Conversão nativa em PHP (Zip + XML).</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <article class="docx-reader" data-text-reader><?= $docxHtml ?></article>
                            <?php endif; ?>
                        <?php elseif ($viewerType === 'pdf'): ?>
                            <div class="pdf-viewer" data-pdf-viewer>
                                <div class="pdf-scroll" data-pdf-scroll tabindex="0" role="document" aria-label="Documento PDF. Use as setas para navegar, o sinal de mais para ampliar e F para tela cheia.">
                                    <div class="pdf-pages" data-pdf-pages></div>
                                </div>
                                <div class="file-viewer-loading" data-pdf-loading role="status">
                                    <span class="reader-spinner" aria-hidden="true"></span>
                                    <span data-pdf-loading-text>Carregando PDF…</span>
                                </div>
                                <p class="file-viewer-error d-none" data-pdf-error role="alert"></p>
                            </div>
                        <?php elseif ($viewerType === 'image'): ?>
                            <div class="image-viewer-wrap" data-image-wrap>
                                <img src="<?= htmlspecialchars($serveUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>" class="image-viewer-img" data-image-view draggable="false">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <footer class="reader-footer">
                    <div class="reader-progress" role="progressbar" aria-label="Progresso de leitura" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) round($initialPercent) ?>" data-reader-progress>
                        <span class="reader-progress-bar" data-reader-progress-bar style="width: <?= max(0, min(100, $initialPercent)) ?>%"></span>
                    </div>
                    <p class="reader-status" data-reader-status aria-live="polite">
                        <?php if ($viewerType === 'pdf'): ?>
                            Página <?= $initialPage ?><?= $totalPages > 0 ? ' de ' . $totalPages : '' ?>
                        <?php elseif ($viewerType === 'epub'): ?>
                            Preparando leitura…
                        <?php else: ?>
                            Pronto para ler
                        <?php endif; ?>
                    </p>
                </footer>
            </section>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<dialog class="reader-dialog" id="reader-bookmark-dialog" aria-labelledby="reader-bookmark-dialog-title">
    <form method="dialog" data-reader-bookmark-form>
        <h2 id="reader-bookmark-dialog-title">Novo marcador</h2>
        <p class="reader-dialog-hint">Dê um nome para encontrar este ponto depois.</p>
        <label class="reader-field" for="reader-bookmark-title">
            <span>Nome do marcador</span>
            <input id="reader-bookmark-title" type="text" maxlength="150" autocomplete="off" placeholder="Ex.: Capítulo 2 — conclusão" required>
        </label>
        <div class="reader-dialog-actions">
            <button type="button" class="btn btn-light border" data-dialog-cancel>Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar marcador</button>
        </div>
    </form>
</dialog>

<dialog class="reader-dialog" id="reader-shortcuts-dialog" aria-labelledby="reader-shortcuts-title">
    <h2 id="reader-shortcuts-title">Atalhos de teclado</h2>
    <ul class="reader-shortcuts-list">
        <li><kbd>←</kbd> <kbd>→</kbd><span>Página anterior / próxima</span></li>
        <li><kbd>Page Up</kbd> <kbd>Page Down</kbd><span>Navegar por páginas</span></li>
        <li><kbd>Home</kbd> <kbd>End</kbd><span>Primeira / última página</span></li>
        <li><kbd>+</kbd> <kbd>-</kbd><span>Ampliar / reduzir</span></li>
        <li><kbd>F</kbd><span>Tela cheia</span></li>
        <li><kbd>T</kbd><span>Alternar tema de leitura</span></li>
        <li><kbd>?</kbd><span>Abrir esta ajuda</span></li>
        <li><kbd>Esc</kbd><span>Fechar painéis e diálogos</span></li>
    </ul>
    <div class="reader-dialog-actions">
        <button type="button" class="btn btn-primary" data-dialog-close>Entendi</button>
    </div>
</dialog>

<div class="visually-hidden" aria-live="polite" aria-atomic="true" data-reader-announcer></div>

<?php if ($viewerType === 'pdf'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<?php endif; ?>
<script>
window.PracticeDayFileViewer = {
    type: <?= json_encode($viewerType, JSON_UNESCAPED_UNICODE) ?>,
    title: <?= json_encode($fileName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    ext: <?= json_encode($extLabel, JSON_UNESCAPED_UNICODE) ?>,
    serveUrl: <?= json_encode($serveUrl, JSON_UNESCAPED_UNICODE) ?>,
    downloadUrl: <?= json_encode($downloadUrl, JSON_UNESCAPED_UNICODE) ?>,
    coverUrl: <?= json_encode($coverUrl ?? '', JSON_UNESCAPED_UNICODE) ?>,
    csrfToken: <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>,
    library: <?= json_encode($library['progresso'] ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    bookmarks: <?= json_encode($library['marcadores'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    libraryApi: <?= json_encode($libraryApi ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    initialPage: <?= (int) $initialPage ?>,
    totalPages: <?= (int) $totalPages ?>
};
</script>
<script src="<?= Url::asset('assets/js/file-viewer.js') ?>"></script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
