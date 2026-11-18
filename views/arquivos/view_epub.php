<?php
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

$fileName = (string) ($file['nome_original'] ?? 'Livro');
$disciplina = (string) ($file['disciplina_nome'] ?? 'Sem disciplina');
$libId = (string) ($library['id'] ?? '');
$progresso = is_array($library['progresso'] ?? null) ? $library['progresso'] : [];
$initialPercent = max(0, min(100, (float) ($progresso['progresso_porcentagem'] ?? 0)));
$initialCfi = (string) ($progresso['cfi'] ?? '');

$extraStyles = [Url::asset('assets/css/file-viewer.css')];

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell file-viewer-page file-epub-page">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content reader-page">
            <a class="reader-skip-link" href="#reader-stage">Pular para o conteúdo do livro</a>

            <header class="reader-heading">
                <div class="reader-heading-main">
                    <a class="back-link" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>Voltar
                    </a>
                    <div class="reader-heading-text">
                        <p class="eyebrow">Biblioteca · Leitor EPUB</p>
                        <h1 class="reader-title"><?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>

                <div class="reader-heading-meta" aria-label="Informações do livro">
                    <span class="reader-chip"><i class="bi bi-book-half" aria-hidden="true"></i>EPUB</span>
                    <span class="reader-chip"><i class="bi bi-hdd" aria-hidden="true"></i><?= $formatBytes((int) ($file['tamanho_bytes'] ?? 0)) ?></span>
                    <span class="reader-chip"><i class="bi bi-mortarboard" aria-hidden="true"></i><?= htmlspecialchars($disciplina, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="reader-chip reader-chip-progress"><i class="bi bi-bookmark-check" aria-hidden="true"></i><span data-reader-progress-label><?= number_format($initialPercent, 1, ',', '.') ?>%</span></span>
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

            <section class="reader-shell" data-reader-shell data-reader-type="epub" data-flow="paged" data-reader-theme="light" aria-label="Leitor de livros EPUB">
                <div class="reader-toolbar" role="toolbar" aria-label="Ferramentas de leitura">
                    <div class="reader-toolbar-group">
                        <button type="button" class="reader-btn" data-reader-panel-toggle aria-expanded="false" aria-controls="reader-panel" title="Abrir painel de leitura" aria-label="Abrir painel de leitura">
                            <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-prev title="Capítulo anterior (seta esquerda)" aria-label="Capítulo anterior" aria-keyshortcuts="ArrowLeft PageUp">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-next title="Próximo capítulo (seta direita)" aria-label="Próximo capítulo" aria-keyshortcuts="ArrowRight PageDown">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                        <span class="reader-location" data-epub-location aria-live="polite">Preparando leitura…</span>
                    </div>

                    <div class="reader-toolbar-group reader-toolbar-group-end">
                        <button type="button" class="reader-btn" data-epub-toc-toggle title="Sumário" aria-label="Abrir sumário">
                            <i class="bi bi-list-nested" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-flow-toggle title="Alternar entre modo paginado e rolagem" aria-label="Alternar modo de leitura" aria-pressed="false">
                            <i class="bi bi-arrows-move" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="reader-btn" data-reader-bookmark title="Criar marcador" aria-label="Criar marcador nesta posição">
                            <i class="bi bi-bookmark-plus" aria-hidden="true"></i>
                        </button>
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
                            <button type="button" class="reader-panel-tab" role="tab" aria-selected="false" aria-controls="reader-toc-section" data-reader-tab="toc">Sumário</button>
                            <button type="button" class="reader-panel-tab" role="tab" aria-selected="false" aria-controls="reader-notes-section" data-reader-tab="notes">Anotações</button>
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

                            <label class="reader-field" for="reader-flow-select">
                                <span>Modo de leitura</span>
                                <select id="reader-flow-select" data-reader-flow-select>
                                    <option value="paged">Página a página</option>
                                    <option value="scroll">Rolagem contínua</option>
                                </select>
                            </label>

                            <div class="reader-panel-hint">
                                <i class="bi bi-keyboard" aria-hidden="true"></i>
                                <p><button type="button" class="reader-link-button" data-reader-shortcuts>Abrir atalhos de teclado</button></p>
                            </div>
                        </section>

                        <section class="reader-panel-section" id="reader-toc-section" role="tabpanel" data-reader-section="toc" hidden>
                            <h2 class="reader-panel-title">Sumário</h2>
                            <p class="reader-panel-empty" data-epub-toc-empty>Carregando sumário…</p>
                            <ul class="epub-toc" data-epub-toc></ul>
                        </section>

                        <section class="reader-panel-section" id="reader-notes-section" role="tabpanel" data-reader-section="notes" hidden>
                            <h2 class="reader-panel-title">Seleção</h2>
                            <div class="epub-selection-tools">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-epub-highlight>
                                    <i class="bi bi-highlighter" aria-hidden="true"></i> Destacar trecho
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-epub-note>
                                    <i class="bi bi-journal-text" aria-hidden="true"></i> Anotar trecho
                                </button>
                            </div>

                            <h2 class="reader-panel-title mt-2">Marcadores</h2>
                            <p class="reader-panel-empty" data-epub-bookmarks-empty>Nenhum marcador ainda.</p>
                            <ul class="reader-panel-list" data-epub-bookmarks-list></ul>

                            <h2 class="reader-panel-title mt-2">Destaques</h2>
                            <p class="reader-panel-empty" data-epub-highlights-empty>Nenhum destaque ainda.</p>
                            <ul class="epub-highlight-list" data-epub-highlights-list></ul>

                            <h2 class="reader-panel-title mt-2">Anotações</h2>
                            <p class="reader-panel-empty" data-epub-notes-empty>Nenhuma anotação ainda.</p>
                            <ul class="reader-panel-list" data-epub-notes-list></ul>
                        </section>
                    </aside>

                    <div class="reader-stage epub-stage" id="reader-stage" data-viewer-stage data-viewer-type="epub" tabindex="0" role="region" aria-label="Conteúdo do livro">
                        <div id="epub-viewer" class="epub-viewer-host"></div>
                        <div class="epub-loading" data-epub-loading role="status">
                            <img class="epub-loading-cover" src="<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Capa de <?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>" loading="eager">
                            <p class="epub-loading-title"><?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="epub-loading-text">Abrindo o livro…</p>
                        </div>
                    </div>
                </div>

                <footer class="reader-footer">
                    <div class="reader-progress" role="progressbar" aria-label="Progresso de leitura" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) round($initialPercent) ?>" data-reader-progress>
                        <span class="reader-progress-bar" data-reader-progress-bar style="width: <?= max(0, min(100, $initialPercent)) ?>%"></span>
                    </div>
                    <p class="reader-status" data-reader-status aria-live="polite">Preparando leitura…</p>
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

<dialog class="reader-dialog" id="epub-note-dialog" aria-labelledby="epub-note-title">
    <form method="dialog" data-epub-note-form>
        <h2 id="epub-note-title">Nova anotação</h2>
        <p class="reader-dialog-hint" data-epub-note-quote>Selecione um trecho para vincular a anotação.</p>
        <label class="reader-field" for="epub-note-text">
            <span>Comentário</span>
            <input id="epub-note-text" type="text" maxlength="2000" autocomplete="off" placeholder="O que você quer lembrar sobre este trecho?" required>
        </label>
        <div class="reader-dialog-actions">
            <button type="button" class="btn btn-light border" data-dialog-cancel>Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar anotação</button>
        </div>
    </form>
</dialog>

<dialog class="reader-dialog" id="reader-shortcuts-dialog" aria-labelledby="reader-shortcuts-title">
    <h2 id="reader-shortcuts-title">Atalhos de teclado</h2>
    <ul class="reader-shortcuts-list">
        <li><kbd>←</kbd> <kbd>→</kbd><span>Página anterior / próxima</span></li>
        <li><kbd>Page Up</kbd> <kbd>Page Down</kbd><span>Navegar pelo livro</span></li>
        <li><kbd>Home</kbd> <kbd>End</kbd><span>Início / fim do livro</span></li>
        <li><kbd>+</kbd> <kbd>-</kbd><span>Aumentar / reduzir texto</span></li>
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

<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
<script>
window.PracticeDayEpubReader = {
    type: 'epub',
    title: <?= json_encode($fileName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    coverUrl: <?= json_encode($coverUrl, JSON_UNESCAPED_UNICODE) ?>,
    serveUrl: <?= json_encode($serveUrl, JSON_UNESCAPED_UNICODE) ?>,
    csrfToken: <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>,
    libraryId: <?= json_encode($libId, JSON_UNESCAPED_UNICODE) ?>,
    initialCfi: <?= json_encode($initialCfi, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    initial: {
        progresso: <?= json_encode($progresso, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        marcadores: <?= json_encode($library['marcadores'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        destaques: <?= json_encode($library['destaques'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        anotacoes: <?= json_encode($library['anotacoes'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
    },
    api: <?= json_encode($libraryApi ?? [], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="<?= Url::asset('assets/js/file-epub-reader.js') ?>"></script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
